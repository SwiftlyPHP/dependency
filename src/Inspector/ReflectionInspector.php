<?php

namespace Swiftly\Dependency\Inspector;

use Swiftly\Dependency\InspectorInterface;
use Swiftly\Dependency\Exception\UndefinedClassException;
use Swiftly\Dependency\Exception\UndefinedFunctionException;
use Swiftly\Dependency\Exception\UndefinedMethodException;
use Swiftly\Dependency\Exception\CompoundTypeException;
use Swiftly\Dependency\Exception\UnknownTypeException;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Parameter\ArrayParameter;
use Swiftly\Dependency\Parameter\BooleanParameter;
use Swiftly\Dependency\Parameter\MixedParameter;
use Swiftly\Dependency\Parameter\NumericParameter;
use Swiftly\Dependency\Parameter\StringParameter;
use Swiftly\Dependency\Parameter\ObjectParameter;
use Swiftly\Dependency\Parameter\NamedClassParameter;
use Swiftly\Dependency\Type;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionNamedType;

use function is_object;
use function get_class;
use function class_exists;

/**
 * Determines class/method/function parameters using the PHP reflection API
 *
 * @api
 */
class ReflectionInspector implements InspectorInterface
{
    /** {@inheritDoc} */
    public function inspectClass(string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new UndefinedClassException($class);
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return [];
        }

        return $this->inspectFromReflection($constructor);
    }

    /** {@inheritDoc} */
    public function inspectMethod($class, string $method): array
    {
        try {
            $reflection = new ReflectionMethod($class, $method);
        } catch (ReflectionException $e) {
            $class = is_object($class) ? get_class($class) : $class;

            if (!class_exists($class)) {
                throw new UndefinedClassException($class);
            } else {
                throw new UndefinedMethodException($class, $method);
            }
        }

        return $this->inspectFromReflection($reflection);
    }

    /** {@inheritDoc} */
    public function inspectFunction($function): array
    {
        try {
            $reflection = new ReflectionFunction($function);
        } catch (ReflectionException $e) {
            /** @var string $function */
            throw new UndefinedFunctionException($function);
        }

        return $this->inspectFromReflection($reflection);
    }

    /**
     * Return information about the given reflected method or function
     *
     * @param ReflectionFunctionAbstract $reflection Reflected method
     * @return list<Parameter>                       Parameter information
     */
    private function inspectFromReflection(
        ReflectionFunctionAbstract $reflection
    ): array {
        $parameters = [];

        foreach ($reflection->getParameters() as $parameter) {
            $parameters[] = $this->inspectParameter($parameter);
        }

        return $parameters;
    }

    /**
     * Returns information about a single method or function parameter
     *
     * @throws CompoundTypeException If we encounter a union/intersection type
     *
     * @param ReflectionParameter $reflected Reflected parameter
     * @return Parameter                     Parameter information
     */
    private function inspectParameter(ReflectionParameter $reflected): Parameter
    {
        $type = $reflected->getType();

        if ($type !== null && !($type instanceof ReflectionNamedType)) {
            throw new CompoundTypeException(
                $reflected->getName(),
                $reflected->getDeclaringFunction()
            );
        }

        return $this->parameterFromReflection($reflected, $type);
    }

    /**
     * Return the appropriate Parameter subclass to represent this parameter
     *
     * @php:8.0 Swap to using `match()` statement
     * @param ReflectionParameter $parameter Parameter information
     * @param ?ReflectionNamedType $type     Parameter type information
     */
    private function parameterFromReflection(
        ReflectionParameter $parameter,
        ?ReflectionNamedType $type
    ): Parameter {
        $type_name = $type ? $type->getName() : 'mixed';
        $name = $parameter->getName();
        $nullable = $parameter->allowsNull();
        $default = $this->prepareDefaultCallback($parameter);

        switch ($type_name) {
            case 'array':
                /** @var null|callable():array $default */
                return new ArrayParameter($name, $nullable, $default);
            case 'bool':
                /** @var null|callable():bool $default */
                return new BooleanParameter($name, $nullable, $default);
            case 'mixed':
                /** @var null|callable():mixed $default */
                return new MixedParameter($name, $default);
            case 'int':
            case 'float':
                /** @var null|callable():(int|float) $default */
                return new NumericParameter(
                    $name,
                    $type_name,
                    $nullable,
                    $default
                );
            case 'string':
                /** @var null|callable():string $default */
                return new StringParameter($name, $nullable, $default);
            case 'object':
                /** @var null|callable():object $default */
                return new ObjectParameter($name, $nullable, $default);
            default:
                if (!Type::isClassname($type_name)) {
                    throw new UnknownTypeException($name, $type_name);
                }
                /** @var null|callable():object $default */
                return new NamedClassParameter(
                    $name,
                    $type_name,
                    $nullable,
                    $default
                );
        }
    }

    /**
     * Create the callback used to provide the default value
     *
     * @param ReflectionParameter $parameter Parameter information
     * @return null|callable                 Default value provider
     */
    private function prepareDefaultCallback(
        ReflectionParameter $parameter
    ): ?callable {
        return ($parameter->isDefaultValueAvailable()
            ? [$parameter, 'getDefaultValue']
            : null
        );
    }
}
