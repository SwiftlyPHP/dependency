<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use RuntimeException;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Type;

use function sprintf;

/**
 * Exception used to identify a problem with the arguments passed to a function.
 *
 * @api
 */
final class ArgumentException extends RuntimeException
{
    public static function invalidType(Parameter $parameter, mixed $value): self
    {
        return new self(sprintf(
            "Invalid value provided for parameter '\$%s'. It expects '%s' but"
            . " was instead given '%s'.",
            $parameter->name,
            (string) $parameter->type,
            Type::getName($value),
        ));
    }

    public static function noValue(Parameter $parameter): self
    {
        return new self(sprintf(
            "Unable to resolve parameter '\$%s'. It expects '%s' but the"
            . ' container could not find a suitable value.',
            $parameter->name,
            (string) $parameter->type,
        ));
    }
}
