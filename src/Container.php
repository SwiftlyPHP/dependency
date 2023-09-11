<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\InspectorInterface;
use Swiftly\Dependency\Entry;
use Swiftly\Dependency\Type;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\UndefinedStructureException;
use Swiftly\Dependency\ParameterException;
use Swiftly\Dependency\Exception\UndefinedServiceException;
use Swiftly\Dependency\Exception\ServiceInstantiationException;
use Swiftly\Dependency\Exception\UnexpectedTypeException;
use Swiftly\Dependency\Exception\InvalidArgumentException;
use Swiftly\Dependency\Exception\MissingArgumentException;
use Swiftly\Dependency\Exception\NestedServiceException;
use Exception;
use ReflectionClass;

use function array_key_exists;
use function call_user_func_array;

/**
 * Container responsible for storing and creating services
 *
 * @api
 */
final class Container
{
    private InspectorInterface $inspector;

    /** @var array<class-string,Entry> $entries */
    private array $entries;

    /** @var array<class-string,class-string> $aliases */
    private array $aliases;

    /**
     * Create a container that uses the given `$inspector` to resolve parameters
     *
     * @param InspectorInterface $inspector Parameter inspector
     */
    public function __construct(InspectorInterface $inspector)
    {
        $this->inspector = $inspector;
        $this->entries = [];
        $this->aliases = [];
    }

    /**
     * Register a new service with the container
     *
     * If provided, the `$factory` argument should either be a service object or
     * a callable that creates and returns a service object.
     *
     * @template T of object
     * @psalm-param null|T|callable():T $factory
     * @param class-string<T> $service Service type
     * @param null|T|callable $factory Service provider/factory
     * @return Entry<T>                Service entry definition
     */
    public function register(string $service, $factory = null): Entry
    {
        if ($factory && Type::isServiceInstance($factory)) {
            $entry = Entry::fromInstance($service, $factory);
        } else {
            $entry = new Entry($service, $factory);
        }

        return ($this->entries[$service] = $entry);
    }

    /**
     * Create an alias mapping between one service and another
     *
     * @throws UndefinedServiceException
     *          If trying to alias a service that doesn't exist
     *
     * @template T of object
     * @param class-string<T> $service Service name
     * @param class-string<T> $alias   Alias
     * @return self                    Chainable interface
     */
    public function alias(string $service, string $alias): self
    {
        if (!isset($this->entries[$service])) {
            throw new UndefinedServiceException($service);
        }

        $this->aliases[$alias] = $service;

        return $this;
    }

    /**
     * Determine if the given service has been registered
     *
     * @template T of object
     * @psalm-assert-if-true T $this->entries[$service]
     * @param class-string<T> $service Service type
     * @return bool                    Service is registered?
     */
    public function has(string $service): bool
    {
        return isset($this->entries[$this->aliases[$service] ?? $service]);
    }

    /**
     * Return a service of the given type
     *
     * @throws UndefinedServiceException
     *          If no definition is found for the given service
     * @throws ServiceInstantiationException
     *          If an error occured while resolving service requirements
     * @throws UnexpectedTypeException
     *          If a service was created but did not meet the type constraints
     *
     * @template T of object
     * @param class-string<T> $service Service type
     * @return T                       Service object
     */
    public function get(string $service): object
    {
        if (!$this->has($service)) {
            throw new UndefinedServiceException($service);
        }

        // Get the service definition
        $entry = $this->entries[$this->aliases[$service] ?? $service];

        // Get factory (or class constructor)
        $factory_or_class = self::factoryOrClass($entry);

        // Attempt to resolve arguments
        try {
            $parameters = $this->inspect($factory_or_class);
            $parameters = $this->prepare($parameters, $entry->arguments);
        } catch (Exception $e) {
            throw new ServiceInstantiationException($service, $e);
        }

        // Create the object!
        $instance = self::create($factory_or_class, $parameters);
        self::assertType($instance, $service);

        return $instance;
    }

    /**
     * Return all services with a given tag
     *
     * The optional `$type` argument can be used to pass a interface/class
     * constraint that all services must adhere to.
     *
     * @template T of object
     * @psalm-param null|class-string<T> $type
     * @psalm-return ($type is class-string ? list<T> : list<object>)
     * @param non-empty-string $tag   Service tag
     * @param null|class-string $type Interface or class constraint
     * @return object[]               Tagged services
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
     * Return the factory - or if not available the FQN - for this service
     *
     * @template T of object
     * @psalm-return class-string<T>|callable():T
     * @param Entry<T> $entry Service definition
     * @return class-string|callable
     */
    private static function factoryOrClass(Entry $entry)// : string|callable
    {
        return $entry->factory ?: $entry->type;
    }

    /**
     * Inspect the parameters of a class, method or function
     *
     * Accepts class names and all callable types apart from invokable objects.
     *
     * @throws ParameterException
     * @throws UndefinedStructureException
     *
     * @param class-string|callable $class_or_callable Class FQN or callable
     * @return list<Parameter>                         Parameters
     */
    private function inspect($class_or_callable): array
    {
        if (Type::isClassname($class_or_callable)) {
            return $this->inspector->inspectClass($class_or_callable);
        } else if (Type::isMethod($class_or_callable)) {
            return $this->inspector->inspectMethod($class_or_callable[0], $class_or_callable[1]);
        } else {
            /** @psalm-suppress ArgumentTypeCoercion */
            return $this->inspector->inspectFunction($class_or_callable);
        }
    }

    /**
     * Prepares arguments required for a function call
     *
     * @throws NestedServiceException
     * @throws InvalidArgumentException
     * @throws MissingArgumentException
     *
     * @template T
     * @param list<Parameter<T>> $parameters           Parameter information
     * @param array<non-empty-string,mixed> $arguments Provided arguments
     * @return list<T>                                 Resolved arguments
     */
    private function prepare(array $parameters, array $arguments): array
    {
        $prepared = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            // User manually provided args?
            if (array_key_exists($name, $arguments)) {
                $value = $arguments[$name];
            } else {
                $value = $this->findValue($parameter);
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
     * Attempts to find a suitable value for the given parameter
     *
     * @throws NestedServiceException
     * @throws MissingArgumentException
     *
     * @php:8.0 Use mixed return type
     * @template T
     * @param Parameter<T> $parameter Parameter definition
     * @return null|T                 Resolved argument value
     */
    private function findValue(Parameter $parameter)// : mixed
    {
        if (!$parameter->isBuiltin()
            && $this->has(($type = $parameter->getType()))
        ) {
            try {
                return $this->get($type);
            } catch (Exception $e) {
                throw new NestedServiceException($e);
            }
        }

        $value = self::defaultValue($parameter);

        if ($value === null && !$parameter->isNullable()) {
            throw new MissingArgumentException($parameter->getName());
        }

        return $value;
    }

    /**
     * Return the default argument of a parameter
     *
     * @php:8.0 Use mixed return type
     * @template T
     * @param Parameter<T> $parameter Parameter definition
     * @return null|T                 Default value
     */
    private static function defaultValue(Parameter $parameter)// : mixed
    {
        return ($parameter->hasDefault()
            ? ($parameter->getDefaultCallback())()
            : null
        );
    }

    /**
     * Create a service, either by calling the factory or creating an object
     *
     * @template T of object
     * @psalm-param class-string<T>|callable():T $factory_or_class
     * @param class-string|callable $factory_or_class Factory or class FQN
     * @param list<mixed> $arguments                  Arguments
     * @return T
     */
    private static function create($factory_or_class, array $arguments): object
    {
        if (Type::isClassname($factory_or_class)) {
            return self::initialise($factory_or_class, $arguments);
        } else {
            /** @psalm-suppress TooManyArguments */
            return call_user_func_array($factory_or_class, $arguments);
        }
    }

    /**
     * Initialise a new service instance with the given parameters
     *
     * @template T of object
     * @param class-string<T> $class Class FQN
     * @param list<mixed> $arguments Constructor arguments
     * @return T                     Initialised class
     */
    private static function initialise(string $class, array $arguments): object
    {
        return (new ReflectionClass($class))->newInstanceArgs($arguments);
    }

    /**
     * Validate that the given object meets a type constaint
     *
     * @throws UnexpectedTypeException
     *          If the `$service` is not of type `$constraint`
     *
     * @template T of object
     * @template K of object
     * @psalm-param class-string<K> $constraint
     * @psalm-assert T&K $service
     * @param T $service               Service instance
     * @param class-string $constraint Interface or class constaint
     * @return void
     */
    private static function assertType(object $service, string $constraint): void
    {
        if (!($service instanceof $constraint))
            throw new UnexpectedTypeException(
                $constraint,
                Type::getName($service)
            );
    }
}
