<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use RuntimeException;

use function sprintf;

/**
 * Exception used to indicate a provided function argument was invalid
 *
 * @api
 */
final class InvalidArgumentException extends RuntimeException
{
    /**
     * Indicate parameter expected type of `$expected` but received `$provided`
     *
     * @param non-empty-string $parameter Parameter name
     * @param non-empty-string $expected  Expected parameter type
     * @param string $provided            Provided type
     */
    public function __construct(
        string $parameter,
        string $expected,
        string $provided
    ) {
        parent::__construct(
            sprintf(
                "Invalid argument provided for parameter '\$%s', expected %s but received %s instead",
                $parameter,
                $expected,
                $provided
            )
        );
    }
}
