<?php declare(strict_types=1);

namespace Swiftly\Dependency\Inspector;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Swiftly\Dependency\CompoundConstraintInterface;
use Swiftly\Dependency\ConstraintInterface;
use Swiftly\Dependency\Exception\ReflectionException;
use Swiftly\Dependency\InspectorInterface;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Type;
use Swiftly\Dependency\Type\ArrayConstraint;
use Swiftly\Dependency\Type\BoolConstraint;
use Swiftly\Dependency\Type\ClassConstraint;
use Swiftly\Dependency\Type\FloatConstraint;
use Swiftly\Dependency\Type\IntConstraint;
use Swiftly\Dependency\Type\IntersectionConstraint;
use Swiftly\Dependency\Type\MixedConstraint;
use Swiftly\Dependency\Type\ObjectConstraint;
use Swiftly\Dependency\Type\StringConstraint;
use Swiftly\Dependency\Type\UnionConstraint;

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
    /**
     * @inheritDoc
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
     * @inheritDoc
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

    /**
     * @inheritDoc
     */
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

    private function inspectParameter(ReflectionParameter $parameter): Parameter
    {
        $constraint = $parameter->hasType()
            ? self::constraintFromReflection($parameter, $parameter->getType())
            : new MixedConstraint();

        return new Parameter(
            $parameter->getName(),
            $constraint,
            $parameter->isDefaultValueAvailable(),
            $parameter->allowsNull(),
        );
    }

    /**
     * @throws ReflectionException
     */
    private static function constraintFromReflection(
        ReflectionParameter $parameter,
        ReflectionType $type,
    ): ConstraintInterface|CompoundConstraintInterface {
        if ($type instanceof ReflectionNamedType) {
            return self::mapConstraint($parameter, $type);
        }

        if ($type instanceof ReflectionUnionType) {
            return new UnionConstraint(
                self::mapCompoundConstraint($parameter, $type)
            );
        }

        if ($type instanceof ReflectionIntersectionType) {
            return new IntersectionConstraint(
                self::mapCompoundConstraint($parameter, $type)
            );
        }

        throw ReflectionException::unknownType($parameter);
    }

    /**
     * @throws ReflectionException
     */
    private static function mapConstraint(
        ReflectionParameter $parameter,
        ReflectionNamedType $type,
    ): ConstraintInterface {
        if (!$type->isBuiltin()) {
            return new ClassConstraint($type->getName());
        }

        return match ($type->getName()) {
            Type::TYPE_ARRAY => new ArrayConstraint(),
            Type::TYPE_BOOL => new BoolConstraint(),
            Type::TYPE_FLOAT => new FloatConstraint(),
            Type::TYPE_INT => new IntConstraint(),
            Type::TYPE_MIXED => new MixedConstraint(),
            Type::TYPE_OBJECT => new ObjectConstraint(),
            Type::TYPE_STRING => new StringConstraint(),
            default => throw ReflectionException::unknownType($parameter),
        };
    }

    /**
     * @return non-empty-list<ConstraintInterface|CompoundConstraintInterface>
     */
    private static function mapCompoundConstraint(
        ReflectionParameter $parameter,
        ReflectionUnionType|ReflectionIntersectionType $type,
    ): array {
        $members = [];

        foreach ($type->getTypes() as $type) {
            $members[] = self::constraintFromReflection($parameter, $type);
        }

        return $members;
    }
}
