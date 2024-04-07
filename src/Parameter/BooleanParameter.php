<?php declare(strict_types=1);

namespace Swiftly\Dependency\Parameter;

use Swiftly\Dependency\Parameter;

use function is_scalar;

/**
 * Function parameter that expects boolean values
 *
 * In non-strict mode PHP allows scalar values as arguments for boolean typed
 * parameters, hence the call to `is_scalar`.
 *
 * @api
 * @psalm-immutable
 * @extends Parameter<bool>
 */
class BooleanParameter extends Parameter
{
    /** {@inheritDoc} */
    public function getType(): string
    {
        return 'bool';
    }

    /** {@inheritDoc} */
    public function isBuiltin(): bool
    {
        return true;
    }

    /** {@inheritDoc} */
    public function accepts($subject): bool
    {
        return (is_scalar($subject)
            || ($this->isNullable() && $subject === null)
        );
    }
}
