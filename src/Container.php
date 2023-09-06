<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\InspectorInterface;
use Swiftly\Dependency\Entry;
use Swiftly\Dependency\Type;
use Swiftly\Dependency\Exception\UnexpectedTypeException;
use Swiftly\Dependency\Exception\UndefinedServiceException;
use Exception;
use Swiftly\Dependency\Exception\ServiceParameterException;
use Swiftly\Dependency\Exception\UndefinedClassException;
use Swiftly\Dependency\Exception\UndefinedMethodException;
use Swiftly\Dependency\Exception\UndefinedFunctionException;
use Swiftly\Dependency\Exception\CompoundTypeException;
use Swiftly\Dependency\Exception\ServiceArgumentException;
use Swiftly\Dependency\Parameter;

/**
 * Stores runtime service definitions for easy object instantiation
 *
 * @api
 */
class Container
{
    private InspectorInterface $inspector;

    /** @var array<class-string,Entry> $entries */
    private array $entries;

    /** @var array<class-string,object> $cached */
    private array $cached;

    /**
     * Creates a new container with the given parameter resolver
     *
     * In almost all cases you will want to use the `ReflectionInspector` as it
     * will allow you to determine the parameters of all functions and methods
     * on request. However, in scenarios where the container is in the hot path
     * and performance is important you can opt to use one of the cache
     * inspectors instead which *may* reduce overhead.
     *
     * @param InspectorInterface $inspector Parameter inspector
     */
    public function __construct(InspectorInterface $inspector)
    {
        $this->inspector = $inspector;
        $this->entries = [];
        $this->cached = [];
    }

    /**
     * Register a new service entry with this container
     *
     * After calling this method the registered service is then returned,
     * allowing you to make calls to {@see Entry::setTags()} and
     * {@see Entry::setArguments()} for additional configuration.
     *
     * @template T of object
     * @psalm-param null|T|callable():T $factory_or_instance
     *
     * @param class-string<T> $type               Fully qualified classname
     * @param null|T|Closure $factory_or_instance Function or object instance
     * @return Entry                              Newly registered service
     */
    public function register(string $type, $factory_or_instance = null): Entry
    {
        if ($factory_or_instance
            && Type::isServiceInstance($factory_or_instance)
        ) {
            $entry = Entry::fromInstance($type, $factory_or_instance);
        } else {
            $entry = new Entry($type, $factory_or_instance);
        }

        return $this->entries[$type] = $entry;;
    }

    /**
     * Attempt to resolve a service of the given type
     *
     * @throws UnexpectedTypeException   If the service breaks type constraints
     * @throws UndefinedServiceException If the given service doesn't exist
     * @throws ServiceParameterException If parameters cannot be inspected
     * @throws ServiceArgumentException  If no suitable argument values exist
     *
     * @template T of object
     * @param class-string<T> $type Fully qualified classname
     * @return T                    Instantiated service
     */    
    public function get(string $type): object
    {
        if (($cached = $this->getCached($type)) !== null) {
            return $cached;
        }

        if (!isset($this->entries[$type])) {
            throw new UndefinedServiceException($type);
        }

        /** @var Entry<T> $entry */
        $entry = $this->entries[$type];

        return $this->resolve($type, $entry);
    }

    /**
     * Return an already instantiated service from the cache
     *
     * @throws UnexpectedTypeException If the service breaks type constraints
     *
     * @template T of object
     * @param class-string<T> $type Fully qualified classname
     * @return T|null
     */
    private function getCached(string $type): ?object
    {
        if (!isset($this->cached[$type])) {
            return null;
        }

        $cached = $this->cached[$type];

        if (!($cached instanceof $type)) {
            throw new UnexpectedTypeException($type, get_class($cached));
        }

        return $cached;
    }

    /**
     * Instantiate a new service object using the provided definition
     *
     * @throws ServiceParameterException If parameters cannot be inspected
     * @throws ServiceArgumentException  If no suitable argument values exist
     *
     * @template T of object
     * @param class-string<T> $type Fully qualified classname
     * @param Entry<T> $service     Service definition
     * @return T                    Instantiated service
     */
    private function resolve(string $type, Entry $service): object
    {
        $factory_or_classname = $service->factory ?: $service->type;

        try {
            $parameters = $this->getParameters($factory_or_classname);
        } catch (Exception $e) {
            throw new ServiceParameterException($type, $e);
        }

        $arguments = $this->resolveArguments($parameters, $service->arguments);
        $instance = $this->instantiate($factory_or_classname, $arguments);
        
        return ($this->entries[$type] = $instance);
    }

    /**
     * Return information regarding the parameters of the given callable
     *
     * @throws UndefinedClassException    If the given class does not exist
     * @throws UndefinedMethodException   If the given method does not exist
     * @throws UndefinedFunctionException If the given function does not exist
     * @throws CompoundTypeException      If a compound type is encountered
     *
     * @template T of object
     * @psalm-param class-string<T>|callable():T $subject
     * @param class-string<T>|callable $subject Callable variable
     * @return list<Parameter>                  Parameter details
     */
    private function getParameters($subject): array
    {
        if (Type::isClassname($subject)) {
            return $this->inspector->inspectClass($subject);
        } else if (Type::isInvokable($subject)) {
            return $this->inspector->inspectMethod($subject, '__invoke');
        } else if (Type::isMethod($subject)) {
            return $this->inspector->inspectMethod($subject[0], $subject[1]);
        } else {
            return $this->inspector->inspectFunction($subject);
        }
    }

    /**
     * Attempts to resolve function arguments from the supplied parameter info
     *
     * @throws ServiceArgumentException  If no suitable argument values exist
     *
     * @template T
     * @param list<Parameter<T>> $parameters Parameter information
     * @param array<string,T> $arguments     Pre-provided arguments
     * @return list<T>                       Resolved arguments
     */
    private function resolveArguments(
        array $parameters,
        array $arguments = []
    ): array {
        $resolved = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->name;

            if (isset($arguments[$name])) {
                $resolved = $arguments[$name];
            } else {
                $resolved = $this->resolveArgument($parameter);
            }

            if (!$parameter->validate($resolved)) {
                throw new ServiceArgumentException($parameter->name);
            }
        }

        return $resolved;
    }

    /**
     * Attempt to resolve a value for the given parameter
     *
     * @throws ServiceArgumentException If no suitable value can be found
     *
     * @template T
     * @param Parameter<T> $parameter Parameter information
     * @return T                      Resolved argument value
     */
    private function resolveArgument(Parameter $parameter)// : mixed
    {
        if (($type = $parameter->type) === null || $parameter->builtin) {
            throw new ServiceArgumentException($parameter->name);
        }

        try {
            /** @var class-string<T> $type */
            return $this->get($type);
        } catch (Exception $e) {
            throw new ServiceArgumentException($parameter->name, $e);
        }
    }

    /**
     * Return a list of entries filtered by tag
     *
     * @param string $tag  Tag name
     * @return list<Entry> Filtered entries
     */
    private function filterByTag(string $tag): array
    {
        $filtered = [];

        foreach ($this->entries as $entry) {
            if ($entry->hasTag($tag)) $filtered[] = $entry;
        }

        return $filtered;
    }
}
