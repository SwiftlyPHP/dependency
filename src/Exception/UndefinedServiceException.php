<?php

namespace Swiftly\Dependency\Exception;

use RuntimeException;

use function sprintf;

/**
 * Exception used to indicate that a named service is undefined
 *
 * @api
 */
final class UndefinedServiceException extends RuntimeException
{
    /**
     * Indicate the given service is undefined
     *
     * @param class-string $service Fully qualified name
     */
    public function __construct(string $service)
    {
        parent::__construct(
            sprintf(
                "Could not resolve '%s' no matching service definitions found",
                $service
            )
        );
    }
}
