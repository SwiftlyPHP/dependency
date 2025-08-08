<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use ReflectionFunctionAbstract;
use ReflectionParameter;
use Swiftly\Dependency\Debug;

use function sprintf;

/**
 * Errors thrown while inspecting the parameters of a constructor or function.
 *
 * This exception likely indicates that a change in logic is required.
 *
 * @api
 */
final class ReflectionException extends \ReflectionException
{
    public static function missingClass(string $class): self
    {
        return new self(sprintf(
            "Could not find a declaration for class '%s', are you sure it"
            . ' exists and that it has been autoloaded?',
            $class,
        ));
    }

    /**
     * @param class-string $class
     */
    public static function missingMethod(string $class, string $method): self
    {
        return new self(sprintf(
            "Could not find a declaration for method '%s::%s()', are you sure it"
            . ' exists on the named class?',
            $class,
            $method,
        ));
    }

    public static function missingFunction(string $function): self
    {
        return new self(sprintf(
            "Could not find a declaration for function '%s()', are you sure it"
            . ' exists and that its containing file has been included?',
            $function,
        ));
    }

    /**
     * This library does not (yet) support resolving union or compound types.
     *
     * @param non-empty-string $parameter
     */
    public static function compoundType(
        ReflectionFunctionAbstract $function,
        string $parameter,
    ): self {
        return new self(sprintf(
            "Unable to resolve complex type of parameter '\$%s' of function"
            . " '%s()'. Please either remove the union/compound type hint or"
            . ' use one of the #[Resolve\Use*] attributes.',
            $parameter,
            Debug::getFunctionName($function),
        ));
    }

    public static function unknownType(ReflectionParameter $parameter): self
    {
        return new self(sprintf(
            "Unable to determine the type expected by parameter '\$%s' of"
            . " function '%s()'. In most cases this is because it expects"
            . ' either an Enum or resource.',
            $parameter->getName(),
            Debug::getFunctionName($parameter->getDeclaringFunction()),
        ));
    }
}
