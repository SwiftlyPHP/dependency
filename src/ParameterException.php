<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use RuntimeException;

/**
 * Interface for all exceptions that can occur while inspecting parameters.
 *
 * @upgrade:php8.2 Mark class as readonly
 *
 * @api
 */
abstract class ParameterException extends RuntimeException
{
    /** @var non-empty-string $parameter */
    protected readonly string $parameter;

    /**
     * Return the parameter we were inspecting when this exception occurred.
     *
     * @return non-empty-string
     */
    public function getParameterName(): string
    {
        return $this->parameter;
    }
}
