<?php

namespace Swiftly\Dependency\Parameter;

use Swiftly\Dependency\Parameter;

use function is_scalar;

/**
 * Function parameter that expects string values
 *
 * While the check to `is_scalar` may seem problematic (as it means boolean
 * values are considered valid) this mirrors the behaviour of PHP in non-strict
 * mode.
 * 
 * The guarantee the {@see Parameter} contract provides is merely that any value
 * that satisfies the {@see Parameter::accepts} condition will not cause a
 * runtime error when passed as an argument to the inspected function, it does
 * not validate how reasonable or correct the value seems.
 *
 * @api
 * @psalm-immutable
 * @extends Parameter<string>
 */
class StringParameter extends Parameter
{
    /** {@inheritDoc} */
    public function getType(): string
    {
        return 'string';
    }

    /** {@inheritDoc} */
    public function isBuiltin(): bool
    {
        return true;
    }

    /** {@inheritDoc} */
    public function accepts($subject): bool
    {
        return is_scalar($subject);
    }
}
