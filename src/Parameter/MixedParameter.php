<?php

namespace Swiftly\Dependency\Parameter;

use Swiftly\Dependency\Parameter;

/**
 * Function parameter that has no specific type requirements
 *
 * @api
 * @extends Parameter<mixed>
 */
final class MixedParameter extends Parameter
{
    /**
     * Create a parameter that accepts any type
     *
     * @psalm-param null|callable():mixed $default
     * @param non-empty-string $name Parameter name
     * @param null|callable $default Default value provider function
     */
    public function __construct(string $name, $default = null)
    {
        parent::__construct($name, true, $default);
    }

    /** {@inheritDoc} */
    public function getType(): string
    {
        return 'mixed';
    }

    /** {@inheritDoc} */
    public function isBuiltin(): bool
    {
        return false;
    }

    /** {@inheritDoc} */
    public function accepts($subject): bool
    {
        return true;
    }
}
