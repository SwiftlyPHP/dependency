<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Closure;
use Exception;
use ReflectionClass;
use Swiftly\Dependency\Exception\ContainerException;
use Swiftly\Dependency\Exception\InvalidArgumentException;
use Swiftly\Dependency\Exception\MissingArgumentException;
use Swiftly\Dependency\Exception\NestedServiceException;
use Swiftly\Dependency\Exception\ReflectionException;
use Swiftly\Dependency\Exception\ServiceInstantiationException;
use Swiftly\Dependency\Inspector\ReflectionInspector;
use Swiftly\Dependency\InspectorInterface;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Type;

use function array_key_exists;
use function assert;
use function call_user_func_array;

/**
 * Container responsible for storing and creating services.
 *
 * @api
 */
class Container
{
    /** @var array<class-string,Recipe> */
    private array $recipes = [];

    /** @var array<class-string,class-string> */
    private array $aliases = [];

    /** @var array<class-string,object> */
    private array $cache = [];

    /**
     * Create a new service container.
     *
     * By default the `ReflectionInspector` is used, but if you need to supply
     * your own inspector you may pass it in here.
     *
     * @param $inspector Parameter inspector.
     */
    public function __construct(
        private InspectorInterface $inspector = new ReflectionInspector(),
    ) {
    }

    /**
     * Register a new service with the container.
     *
     * If provided, the `$factory` argument should either be a service object or
     * a callable that creates and returns a service object.
     *
     * @template T of object
     *
     * @param class-string<T> $service
     * @param null|T|Closure(mixed):T $factory
     *
     * @return Recipe<T>
     */
    public function register(
        string $service,
        object|null $factory = null,
    ): Recipe {
        return $this->recipes[$service] = new Recipe(
            $service,
            null !== $factory && Type::isServiceInstance($factory)
                ? static fn () => $factory
                : $factory,
        );
    }

    /**
     * Create an alias mapping between one service and another.
     *
     * @template T of object
     *
     * @param class-string<T> $service
     * @param class-string<T> $alias
     *
     * @throws ContainerException When trying to alias a non-existent service.
     *
     * @return self
     */
    public function alias(string $service, string $alias): self
    {
        if (!isset($this->recipes[$service])) {
            throw ContainerException::missingService($service);
        }

        $this->aliases[$alias] = $service;

        return $this;
    }

    /**
     * Determine if the given service has been registered.
     *
     * @template T of object
     * @psalm-assert-if-true T $this->recipes[$service]
     *
     * @param class-string<T> $service Service type.
     *
     * @return bool                    Service is registered?
     */
    public function has(string $service): bool
    {
        return isset($this->recipes[$this->aliases[$service] ?? $service]);
    }

    /**
     * Return a service of the given type.
     *
     * @template T of object
     *
     * @param class-string<T> $service
     *
     * @throws ContainerException When trying to fetch a unknown service.
     * @throws ContainerException If the service type constraint is broken.
     *
     * @return T
     */
    public function get(string $service): object
    {
        if (!$this->has($service)) {
            throw ContainerException::missingService($service);
        }

        $service = $this->aliases[$service] ?? $service;
        $recipe = $this->recipes[$service];

        // Service might be a singleton
        if (isset($this->cache[$service]) && $recipe->isSingleton) {
            $instance = $this->cache[$service];
            self::assertType($instance, $service);
            return $instance;
        }

        $factoryOrClass = self::factoryOrClass($recipe);

        try {
            $parameters = $this->inspect($factoryOrClass);
            $parameters = $this->prepare($parameters, $recipe->defaultArguments);
        } catch (Exception $e) {
            throw new ServiceInstantiationException($service, $e);
        }

        $instance = self::create($factoryOrClass, $parameters);
        self::assertType($instance, $service);
        $this->cache[$service] = $instance;

        return $instance;
    }

    /**
     * Return all services with a given tag.
     *
     * The optional `$type` argument can be used to pass a interface/class
     * constraint that all services must adhere to.
     *
     * @template T of object
     *
     * @param non-empty-string $tag
     * @param null|class-string<T> $type
     *
     * @return object[]
     * @psalm-return ($type is class-string ? list<T> : list<object>)
     */
    public function tagged(string $tag, ?string $type = null): array
    {
        $resolved = [];

        foreach ($this->recipes as $name => $recipe) {
            if (!$recipe->hasTag($tag)) {
                continue;
            }

            $service = $this->get($name);

            if ($type) {
                self::assertType($service, $type);
            }

            $resolved[] = $service;
        }

        return $resolved;
    }

    /**
     * Attempts to resolve and call the provided function.
     *
     * @template T
     *
     * @param callable():T $callback
     * @param array<non-empty-string, mixed> $parameters
     *
     * @return T
     */
    public function call(callable $callback, array $parameters = []): mixed
    {
        $arguments = $this->prepare($this->inspect($callback), $parameters);

        return call_user_func_array($callback, $arguments);
    }

    /**
     * Return the factory - or if not available the FQN - for this service.
     *
     * @template T of object
     *
     * @param Recipe<T> $recipe
     *
     * @return class-string<T>|Closure():T
     */
    protected static function factoryOrClass(Recipe $recipe): callable|string
    {
        return $recipe->factory ?? $recipe->className;
    }

    /**
     * Inspect the parameters of a class, method or function.
     *
     * Accepts class names and all callable types apart from invokable objects.
     *
     * @param class-string|callable $classOrCallable
     *
     * @throws ReflectionException
     *
     * @return list<Parameter>
     */
    protected function inspect($classOrCallable): array
    {
        if (Type::isClassname($classOrCallable)) {
            return $this->inspector->inspectClass($classOrCallable);
        } elseif (Type::isMethod($classOrCallable)) {
            return $this->inspector->inspectMethod($classOrCallable[0], $classOrCallable[1]);
        } else {
            /** @psalm-suppress ArgumentTypeCoercion */
            return $this->inspector->inspectFunction($classOrCallable);
        }
    }

    /**
     * Prepares arguments required for a function call.
     *
     * @template T
     *
     * @param list<Parameter<T>> $parameters
     * @param array<non-empty-string, mixed> $arguments
     *
     * @throws NestedServiceException
     * @throws InvalidArgumentException
     * @throws MissingArgumentException
     *
     * @return list<T>
     */
    protected function prepare(array $parameters, array $arguments): array
    {
        $prepared = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->name;

            // User manually provided args?
            if (array_key_exists($name, $arguments)) {
                $value = $arguments[$name];
            } else {
                $value = $this->resolveParam($parameter);
            }

            if (!$parameter->type->isSatisfiedBy($value)) {
                throw new InvalidArgumentException(
                    $name,
                    $parameter->type->getName(),
                    Type::getName($value),
                );
            }

            $prepared[] = $value;
        }

        return $prepared;
    }

    /**
     * @template T
     *
     * @param Parameter<T> $parameter
     *
     * @throws NestedServiceException
     * @throws MissingArgumentException
     *
     * @return null|T
     */
    protected function resolveParam(Parameter $parameter): mixed
    {
        $type = $parameter->type;

        $resolvedOrNull = $type instanceof CompoundConstraintInterface
            ? $this->resolveOneOf($type->getMembers())
            : $this->resolveSingle($type);

        if (null === $resolvedOrNull) {
            $resolvedOrNull = self::defaultValue($parameter);
        }

        return $resolvedOrNull;
    }

    /**
     * @template T
     *
     * @param non-empty-list<ConstraintInterface<T>> $types
     *
     * @return null|T
     */
    protected function resolveOneOf(array $types): ?object
    {
        foreach ($types as $type) {
            $resolved = $this->resolveSingle($type);

            if (null !== $resolved) {
                return $resolved;
            }
        }

        return null;
    }

    /**
     * @template T
     *
     * @param ConstraintInterface<T> $type
     *
     * @return null|T
     */
    protected function resolveSingle(ConstraintInterface $type): ?object
    {
        if (!$type->isClassType()) {
            return null;
        }

        $typeName = $type->getName();

        assert(Type::isClassname($typeName));

        if (!$this->has($typeName)) {
            return null;
        }

        return $this->get($typeName);
    }

    /**
     * @template T
     *
     * @param Parameter<T> $parameter
     *
     * @return null|T
     */
    protected static function defaultValue(Parameter $parameter): mixed
    {
        return null !== $parameter->defaultValueCallback
            ? ($parameter->defaultValueCallback)()
            : null;
    }

    /**
     * Create a service, either by calling the factory or creating an object.
     *
     * @template T of object
     *
     * @param class-string<T>|callable():T $factoryOrClass
     * @param list<mixed> $arguments
     *
     * @return T
     */
    protected static function create($factoryOrClass, array $arguments): object
    {
        if (Type::isClassname($factoryOrClass)) {
            return self::initialise($factoryOrClass, $arguments);
        } else {
            /** @psalm-suppress TooManyArguments */
            return call_user_func_array($factoryOrClass, $arguments);
        }
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param list<mixed> $arguments
     *
     * @return T
     */
    protected static function initialise(string $class, array $arguments): object
    {
        return (new ReflectionClass($class))->newInstanceArgs($arguments);
    }

    /**
     * @template T of object
     * @template K of object
     * @psalm-assert T&K $service
     *
     * @param T $service
     * @param class-string<K> $constraint
     *
     * @throws ContainerException If the service does not meet the constraint.
     */
    protected static function assertType(object $service, string $constraint): void
    {
        if ($service instanceof $constraint) {
            return;
        }

        throw ContainerException::typeConstraint($service::class, $constraint);
    }
}
