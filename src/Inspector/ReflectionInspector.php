<?php declare(strict_types=1);

namespace Swiftly\Dependency\Inspector;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Swiftly\Dependency\Exception\CompoundTypeException;
use Swiftly\Dependency\Exception\UndefinedStructureException;
use Swiftly\Dependency\Exception\UnknownTypeException;
use Swiftly\Dependency\InspectorInterface;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Parameter\ArrayParameter;
use Swiftly\Dependency\Parameter\BooleanParameter;
use Swiftly\Dependency\Parameter\MixedParameter;
use Swiftly\Dependency\Parameter\NamedClassParameter;
use Swiftly\Dependency\Parameter\NumericParameter;
use Swiftly\Dependency\Parameter\ObjectParameter;
use Swiftly\Dependency\Parameter\StringParameter;
use Swiftly\Dependency\Type;

use function class_exists;
use function get_class;
use function is_object;

/**
 * Determines class/method/function parameters using the PHP reflection API.
 *
 * @api
 *
 * @upgrade:php8.3 Apply #[Override] attribute
 */
class ReflectionInspector implements InspectorInterface
{
    /** {@inheritDoc} */
    public function inspectClass(string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw UndefinedStructureException::createForClass($class);
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return [];
        }

        return $this->inspectFromReflection($constructor);
    }

    /** {@inheritDoc} */
    public function inspectMethod(Object|string $class, string $method): array
    {
        try {
            $reflection = new ReflectionMethod($class, $method);
        } catch (ReflectionException $e) {
            $class = is_object($class) ? get_class($class) : $class;

            if (!class_exists($class)) {
                throw UndefinedStructureException::createForClass($class);
            } else {
                throw UndefinedStructureException::createForMethod(
                    $class,
                    $method
                );
            }
        }

        return $this->inspectFromReflection($reflection);
    }

    /** {@inheritDoc} */
    public function inspectFunction(Closure|string $function): array
    {
        try {
            $reflection = new ReflectionFunction($function);
        } catch (ReflectionException $e) {
            /** @var string $function */
            throw UndefinedStructureException::createForFunction($function);
        }

        return $this->inspectFromReflection($reflection);
    }

    /**
     * Return information about the given reflected method or function.
     *
     * @param ReflectionFunctionAbstract $reflection
     * @return list<Parameter>
     */
    private function inspectFromReflection(
        ReflectionFunctionAbstract $reflection,
    ): array {
        $parameters = [];

        foreach ($reflection->getParameters() as $parameter) {
            $parameters[] = $this->inspectParameter($parameter);
        }

        return $parameters;
    }

    /**
     * Returns information about a single method or function parameter.
     *
     *
     * @param ReflectionParameter $reflected
     * @throws CompoundTypeException If we encounter a union/intersection type.
     *
     * @return Parameter
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
     * Return the appropriate Parameter subclass to represent this parameter.
     *
     * @param ReflectionParameter $parameter
     * @param ReflectionNamedType|null $type
     */
    private function parameterFromReflection(
        ReflectionParameter $parameter,
        ReflectionNamedType|null $type,
    ): Parameter {
        $type_name = $type ? $type->getName() : Type::TYPE_MIXED;
        $name = $parameter->getName();
        $nullable = $parameter->allowsNull();
        $default = $this->prepareDefaultCallback($parameter);

        return match ($type_name) {
            /** @var null|callable():array $default */
            Type::TYPE_ARRAY => new ArrayParameter($name, $nullable, $default),

            /** @var null|callable():bool $default */
            Type::TYPE_BOOL => new BooleanParameter($name, $nullable, $default),

            /** @var null|callable():mixed $default */
            Type::TYPE_MIXED => new MixedParameter($name, $default),

            /** @var null|callable():(int|float) $default */
            Type::TYPE_INT,
            Type::TYPE_FLOAT =>
                new NumericParameter($name, $type_name, $nullable, $default),

            /** @var null|callable():string $default */
            Type::TYPE_STRING => new StringParameter($name, $nullable, $default),

            /** @var null|callable():object $default */
            Type::TYPE_OBJECT => new ObjectParameter($name, $nullable, $default),

            /** @var null|callable():object $default */
            default => Type::isClassname($type_name)
                ? new NamedClassParameter($name, $type_name, $nullable, $default)
                : throw new UnknownTypeException($name, $type_name),
        };
    }

    /**
     * Create the callback used to provide the default value.
     *
     * @param ReflectionParameter $parameter Parameter information.
     * @return null|callable                 Default value provider.
     */
    private function prepareDefaultCallback(
        ReflectionParameter $parameter
    ): ?callable {
        return $parameter->isDefaultValueAvailable()
            ? [$parameter, 'getDefaultValue']
            : null;
    }
}
