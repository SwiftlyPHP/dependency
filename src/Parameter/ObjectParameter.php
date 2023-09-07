<?php

namespace Swiftly\Dependency\Parameter;

use Swiftly\Dependency\Parameter;

use function is_object;

/**
 * Function parameter that expects an object
 *
 * @api
 * @extends Parameter<object>
 */
class ObjectParameter extends Parameter
{
    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return 'object';
    }

    /** {@inheritDoc} */
    public function isBuiltin(): bool
    {
        return false;
    }

    /** {@inheritDoc} */
    public function accepts($subject): bool
    {
        return is_object($subject);
    }
}
