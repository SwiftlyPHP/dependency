<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use RuntimeException;

use function sprintf;

/**
 * Exception used to indicate no value was provided for a required argument
 *
 * @api
 */
final class MissingArgumentException extends RuntimeException
{
    /**
     * Indicate that a value is required for `$parameter` but none was provided
     *
     * @param non-empty-string $parameter Parameter name
     */
    public function __construct(string $parameter)
    {
        parent::__construct(
            sprintf(
                "Could not create service as no value provided for required parameter '\$%s'",
                $parameter
            )
        );
    }
}
