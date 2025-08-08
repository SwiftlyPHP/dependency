<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Exception;
use ReflectionClass;
use Swiftly\Dependency\Entry;
use Swiftly\Dependency\Exception\AttributeException;
use Swiftly\Dependency\Exception\ContainerException;
use Swiftly\Dependency\Exception\InvalidArgumentException;
use Swiftly\Dependency\Exception\MissingArgumentException;
use Swiftly\Dependency\Exception\NestedServiceException;
use Swiftly\Dependency\Exception\ServiceInstantiationException;
use Swiftly\Dependency\Inspector\ReflectionInspector;
use Swiftly\Dependency\InspectorInterface;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\ParameterException;
use Swiftly\Dependency\Type;

use function array_key_exists;
use function call_user_func_array;

/**
 * Container responsible for storing and creating services.
 *
 * @api
 *
 * @upgrade:php8.1 Drop null hint and use `new` in constructor
 */
class Container
{
    private InspectorInterface $inspector;

    /** @var array<class-string, Entry> $entries */
    private array $entries;

    /** @var array<class-string, class-string> $aliases */
    private array $aliases;

    /** @var array<class-string, object> $cache */
    private array $cache;

    /**
     * Create a new service container.
     *
     * By default the `ReflectionInspector` is used, but if you need to supply
     * your own inspector you may pass it in here.
     *
     * @param InspectorInterface|null $inspector Parameter inspector.
     */
    public function __construct(?InspectorInterface $inspector = null)
    {
        $this->inspector = $inspector ?? new ReflectionInspector();
        $this->entries = [];
        $this->aliases = [];
        $this->cache = [];
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
     * @param null|T|ProvideInterface<T>|callable(mixed):T $factory
     *
     * @return Entry<T>
     */
    public function register(
        string $service,
        object|callable|null $factory = null,
    ): Entry {
        if ($factory && Type::isServiceInstance($factory)) {
            $entry = Entry::fromInstance($service, $factory);
        } else {
            $entry = new Entry($service, $factory);
        }

        return ($this->entries[$service] = $entry);
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
        if (!isset($this->entries[$service])) {
            throw ContainerException::missingService($service);
        }

        $this->aliases[$alias] = $service;

        return $this;
    }

    /**
     * Determine if the given service has been registered.
     *
     * @template T of object
     * @psalm-assert-if-true T $this->entries[$service]
     *
     * @param class-string<T> $service Service type.
     *
     * @return bool                    Service is registered?
     */
    public function has(string $service): bool
    {
        return isset($this->entries[$this->aliases[$service] ?? $service]);
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
        $entry = $this->entries[$service];

        // Service might be a singleton
        if (isset($this->cache[$service]) && $entry->once) {
            $instance = $this->cache[$service];
            self::assertType($instance, $service);
            return $instance;
        }

        $factoryOrClass = self::factoryOrClass($entry);

        try {
            $parameters = $this->inspect($factoryOrClass);
            $parameters = $this->prepare($parameters, $entry->arguments);
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

        foreach ($this->entries as $name => $entry) {
            if (!$entry->hasTag($tag)) {
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
     * @param Entry<T> $entry Service definition
     * @return class-string|callable
     * @psalm-return class-string<T>|callable():T
     */
    protected static function factoryOrClass(Entry $entry): callable|string
    {
        return $entry->factory ?? $entry->type;
    }

    /**
     * Inspect the parameters of a class, method or function.
     *
     * Accepts class names and all callable types apart from invokable objects.
     *
     * @param class-string|callable $class_or_callable
     *
     * @throws ParameterException
     *
     * @return list<Parameter>
     */
    protected function inspect($class_or_callable): array
    {
        if (Type::isClassname($class_or_callable)) {
            return $this->inspector->inspectClass($class_or_callable);
        } elseif (Type::isMethod($class_or_callable)) {
            return $this->inspector->inspectMethod($class_or_callable[0], $class_or_callable[1]);
        } else {
            /** @psalm-suppress ArgumentTypeCoercion */
            return $this->inspector->inspectFunction($class_or_callable);
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
            $name = $parameter->getName();

            // User manually provided args?
            if (array_key_exists($name, $arguments)) {
                $value = $arguments[$name];
            } else {
                $value = $this->resolveParam($parameter);
            }

            if (!$parameter->accepts($value)) {
                throw new InvalidArgumentException(
                    $name,
                    $parameter->getType(),
                    Type::getName($value)
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
        $provider = $parameter->getAttribute(ProvideInterface::class);

        if (null !== $provider) {
            return $this->resolveProvider($parameter, $provider->newInstance());
        }

        $typeHint = $parameter->isBuiltin() ? $parameter->getType() : null;

        if (null !== $typeHint && $this->has($typeHint)) {
            try {
                return $this->get($typeHint);
            } catch (Exception $e) {
                throw new NestedServiceException($e);
            }
        }

        $value = self::defaultValue($parameter);

        if (null === $value && !$parameter->isNullable()) {
            throw new MissingArgumentException($parameter->getName());
        }

        return $value;
    }

    /**
     * @template T
     * @template K
     *
     * @param Parameter<T> $parameter
     * @param ProvideInterface<K> $provider
     *
     * @throws AttributeException
     *
     * @return (T&K)
     */
    protected function resolveProvider(
        Parameter $parameter,
        ProvideInterface $provider,
    ): mixed {
        $value = $provider->provide($this);

        if (!$parameter->accepts($value)) {
            throw AttributeException::typeError($provider, $parameter, $value);
        }

        return $value;
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
        return $parameter->hasDefault()
            ? ($parameter->getDefaultCallback())()
            : null;
    }

    /**
     * Create a service, either by calling the factory or creating an object.
     *
     * @template T of object
     * @param class-string|callable $factory_or_class Factory or class FQN.
     * @param list<mixed> $arguments                  Arguments.
     * @psalm-param class-string<T>|callable():T $factory_or_class
     * @return T
     */
    protected static function create($factory_or_class, array $arguments): object
    {
        if (Type::isClassname($factory_or_class)) {
            return self::initialise($factory_or_class, $arguments);
        } else {
            /** @psalm-suppress TooManyArguments */
            return call_user_func_array($factory_or_class, $arguments);
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
        if (!($service instanceof $constraint)) {
            return;
        }

        throw ContainerException::typeConstraint($service::class, $constraint);
    }
}
