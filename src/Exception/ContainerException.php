<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use LogicException;

use function sprintf;

/**
 * Indicates general errors that occured in the container.
 *
 * These should normally lead to a code change.
 *
 * @api
 */
final class ContainerException extends LogicException
{
    /**
     * @param class-string $service
     */
    public static function missingService(string $service): self
    {
        return new self(sprintf(
            "Could not resolve service '%s' as no service by that name or alias"
            . ' has been registered with the container.',
            $service,
        ));
    }

    public static function typeConstraint(
        string $actual,
        string $expected,
    ): self {
        return new self(sprintf(
            "Successfully resolved '%s' but it does not conform to the"
            . " expected type of '%s'. If you have registered an interface with"
            . ' the container please make sure it resolves to the correct'
            . ' concrete class.',
            $actual,
            $expected,
        ));
    }
}
