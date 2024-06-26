<?php declare(strict_types=1);

namespace Swiftly\Dependency\Parameter;

use Swiftly\Dependency\Parameter;

use function is_object;

/**
 * Function parameter that expects an object
 *
 * @api
 * @psalm-immutable
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
        return (is_object($subject)
            || ($this->isNullable() && $subject === null)
        );
    }
}
