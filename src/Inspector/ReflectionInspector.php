<?php

namespace Swiftly\Dependency\Inspector;

use Swiftly\Dependency\InspectorInterface;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Exception\UndefinedClassException;
use Swiftly\Dependency\Exception\UndefinedFunctionException;
use Swiftly\Dependency\Exception\UndefinedMethodException;
use Swiftly\Dependency\Exception\CompoundTypeException;
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

        if ($reflected->isDefaultValueAvailable()) {
            /** @psalm-suppress MixedAssignment */
            $default = $reflected->getDefaultValue();
        } else {
            $default = null;
        }

        return new Parameter(
            $reflected->getName(),
            ($type ? $type->getName() : $type),
            $default,
            // No type so we must assume it's a native type
            ($type ? $type->isBuiltin() : true)
        );
    }
}
