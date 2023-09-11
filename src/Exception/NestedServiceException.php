<?php

namespace Swiftly\Dependency\Exception;

use RuntimeException;
use Exception;

/**
 * Wrapper user to let us pass exception information to the top-most scope
 *
 * @internal
 */
final class NestedServiceException extends RuntimeException
{
    /** @var non-empty-string $service */
    private string $service;

    /**
     * @param non-empty-string $service Service name
     * @param Exception $reason         Failure reason
     */
    public function __construct(string $service, Exception $reason)
    {
        $this->service = $service;
        parent::__construct("", 0, $reason);
    }

    /**
     * Return the name of the service that triggered this exception
     *
     * @return non-empty-string Service name
     */
    public function getService(): string
    {
        return $this->service;
    }
}
