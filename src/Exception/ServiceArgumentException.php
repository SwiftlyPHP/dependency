<?php

namespace Swiftly\Dependency\Exception;

use RuntimeException;
use Exception;

use function sprintf;

/**
 * Exception used to indicate we could not find a value for a service argument
 *
 * @api
 */
final class ServiceArgumentException extends RuntimeException
{
    /**
     * Indicate no value could satisfy the given parameter
     *
     * @param string $parameter    Parameter name
     * @param ?Exception $previous Underlying exception
     */
    public function __construct(string $parameter, Exception $previous = null)
    {
        parent::__construct(
            sprintf(
                "Could not find a suitable value for parameter '\$%s'",
                $parameter
            ),
            0,
            $previous
        );
    }
}
