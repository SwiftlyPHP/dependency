<?php

namespace Swiftly\Dependency\Resolver;

use Swiftly\Dependency\ResolverInterface;
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
 * Determines class/method/function parameters using reflection
 *
 * @api
 */
class ReflectionResolver implements ResolverInterface
{
    /** {@inheritDoc} */
    public function resolveClass(string $class): array
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

        return $this->resolveFromReflection($constructor);
    }

    /** {@inheritDoc} */
    public function resolveMethod($class, string $method): array
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

        return $this->resolveFromReflection($reflection);
    }

    /** {@inheritDoc} */
    public function resolveFunction($function): array
    {
        try {
            $reflection = new ReflectionFunction($function);
        } catch (ReflectionException $e) {
            /** @var string $function */
            throw new UndefinedFunctionException($function);
        }

        return $this->resolveFromReflection($reflection);
    }

    /**
     * Return information about the given reflected method or function
     *
     * @param ReflectionFunctionAbstract $reflection Reflected method
     * @return list<Parameter>                       Parameter information
     */
    private function resolveFromReflection(
        ReflectionFunctionAbstract $reflection
    ): array {
        $parameters = [];

        foreach ($reflection->getParameters() as $parameter) {
            $parameters[] = $this->resolveParameter($parameter);
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
    private function resolveParameter(ReflectionParameter $reflected): Parameter
    {
        $type = $reflected->getType();

        if ($type !== null && !($type instanceof ReflectionNamedType)) {
            throw new CompoundTypeException($reflected);
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
