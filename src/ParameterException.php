<?php

namespace Swiftly\Dependency;

use RuntimeException;

/**
 * Interface for all exceptions that can occur while inspecting parameters
 *
 * @api
 */
abstract class ParameterException extends RuntimeException
{
    /** @var non-empty-string $parameter */
    protected string $parameter;
    
    /**
     * Return the parameter we were inspecting when this exception occurred
     *
     * @return non-empty-string Case-sensitive parameter name
     */
    public function getParameterName(): string
    {
        return $this->parameter;
    }
}
