<?php declare(strict_types=1);

namespace Swiftly\Dependency\Parameter;

use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Type;

use function is_array;

/**
 * Function parameter that expects array values.
 *
 * @api
 * @psalm-immutable
 * @extends Parameter<array>
 */
class ArrayParameter extends Parameter
{
    /** {@inheritDoc} */
    public function getType(): string
    {
        return Type::TYPE_ARRAY;
    }

    /** {@inheritDoc} */
    public function isBuiltin(): bool
    {
        return true;
    }

    /** {@inheritDoc} */
    public function accepts(mixed $subject): bool
    {
        return is_array($subject) || ($this->isNullable() && $subject === null);
    }
}
