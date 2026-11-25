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
            . " was instead given '%s'",
            $parameter->name,
            $parameter->type->getName(),
            Type::getName($value),
        ));
    }
}
