<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use RuntimeException;

use function sprintf;

/**
 * Exception used to indicate the container resolved a class of the wrong type
 *
 * @api
 */
final class UnexpectedTypeException extends RuntimeException
{
    /**
     * Indicate service resolution broke class/interface contract
     *
     * @param class-string $expected Expected service type
     * @param class-string $actual   Resolved type
     */
    public function __construct(string $expected, string $actual)
    {
        parent::__construct(
            sprintf(
                "Could not resolve service of type '%s' found '%s' instead",
                $expected,
                $actual
            )
        );
    }
}
