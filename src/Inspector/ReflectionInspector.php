<?php declare(strict_types=1);

namespace Swiftly\Dependency\Inspector;

use Closure;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Swiftly\Dependency\ContainerAwareInterface;
use Swiftly\Dependency\Exception\ReflectionException;
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
use Swiftly\Dependency\ResolvableInterface;
use Swiftly\Dependency\Type;

use function class_exists;
use function count;
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
    /**
     * {@inheritDoc}
     */
    public function inspectClass(string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw ReflectionException::missingClass($class);
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return [];
        }

        return $this->inspectFromReflection($constructor);
    }

    /**
     * {@inheritDoc}
     */
    public function inspectMethod(Object|string $class, string $method): array
    {
        try {
            $reflection = new ReflectionMethod($class, $method);
        } catch (\ReflectionException $e) {
            $class = is_object($class) ? get_class($class) : $class;

            if (!class_exists($class)) {
                throw ReflectionException::missingClass($class);
            } else {
                throw ReflectionException::missingMethod($class, $method);
            }
        }

        return $this->inspectFromReflection($reflection);
    }

    /** {@inheritDoc} */
    public function inspectFunction(Closure|string $function): array
    {
        try {
            $reflection = new ReflectionFunction($function);
        } catch (\ReflectionException $e) {
            /** @var string $function */
            throw ReflectionException::missingFunction($function);
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

    private function inspectParameter(ReflectionParameter $reflected): Parameter
    {
        $type = $reflected->getType();

        if ($type !== null && !($type instanceof ReflectionNamedType)) {
            throw ReflectionException::compoundType(
                $reflected->getDeclaringFunction(),
                $reflected->getName(),
            );
        }

        return $this->parameterFromReflection($reflected, $type);
    }

    /**
     * Return the appropriate subclass to represent this parameter.
     */
    private function parameterFromReflection(
        ReflectionParameter $parameter,
        ReflectionNamedType|null $type,
    ): Parameter {
        $type_name = $type ? $type->getName() : Type::TYPE_MIXED;
        $name = $parameter->getName();
        $nullable = $parameter->allowsNull();
        $default = self::prepareDefaultCallback($parameter);
        $attributes = self::getAttributes($parameter);

        return match ($type_name) {
            Type::TYPE_ARRAY =>
                /** @var null|callable():array $default */
                new ArrayParameter($name, $nullable, $default),

            Type::TYPE_BOOL =>
                /** @var null|callable():bool $default */
                new BooleanParameter($name, $nullable, $default),

            Type::TYPE_MIXED =>
                /** @var null|callable():mixed $default */
                new MixedParameter($name, $default),

            Type::TYPE_INT,
            Type::TYPE_FLOAT =>
                /** @var null|callable():(int|float) $default */
                new NumericParameter($name, $type_name, $nullable, $default),

            Type::TYPE_STRING =>
                /** @var null|callable():string $default */
                new StringParameter($name, $nullable, $default),

            Type::TYPE_OBJECT =>
                /** @var null|callable():object $default */
                new ObjectParameter($name, $nullable, $default),

            default =>
                /** @var null|callable():object $default */
                Type::isClassname($type_name)
                    ? new NamedClassParameter($name, $type_name, $nullable, $default)
                    : throw ReflectionException::unknownType($parameter),
        };
    }

    private static function prepareDefaultCallback(
        ReflectionParameter $parameter,
    ): ?callable {
        return $parameter->isDefaultValueAvailable()
            ? [$parameter, 'getDefaultValue']
            : null;
    }

    /**
     * @return list<ReflectionAttribute<ContainerAwareInterface>>
     */
    private static function getAttributes(ReflectionParameter $parameter): array
    {
        return $parameter->getAttributes(
            ContainerAwareInterface::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );
    }
}
