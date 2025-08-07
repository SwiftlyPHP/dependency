<?php declare(strict_types=1);

namespace Swiftly\Dependency\Parameter;

use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Type;

/**
 * Function parameter that has no specific type requirements.
 *
 * @api
 * @psalm-immutable
 * @extends Parameter<mixed>
 */
final class MixedParameter extends Parameter
{
    /**
     * Create a parameter that accepts any type.
     *
     * @param non-empty-string $name Parameter name
     * @param null|callable $default Default value provider function
     * @psalm-param null|callable():mixed $default
     */
    public function __construct(string $name, $default = null)
    {
        parent::__construct($name, true, $default);
    }

    /** {@inheritDoc} */
    public function getType(): string
    {
        return Type::TYPE_MIXED;
    }

    /** {@inheritDoc} */
    public function isBuiltin(): bool
    {
        return false;
    }

    /** {@inheritDoc} */
    public function accepts(mixed $subject): bool
    {
        return true;
    }
}
