<?php

namespace Swiftly\Dependency\Exception;

use RuntimeException;
use Exception;

use function sprintf;

/**
 * Exception used to indicate we failed to resolve service parameters
 *
 * @api
 */
final class ServiceParameterException extends RuntimeException
{
    /**
     * Indicate a parameter issue occurred while resolving the given service
     *
     * @param class-string $service Fully qualified name
     * @param ?Exception $previous  Underlying exception
     */
    public function __construct(string $service, Exception $previous = null)
    {
        parent::__construct(
            sprintf(
                "Could not determine the parameters required for service '%s'",
                $service
            ),
            0,
            $previous
        );
    }
}
