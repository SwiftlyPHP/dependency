<?php declare(strict_types=1);

namespace Swiftly\Dependency\Type;

use Swiftly\Dependency\ConstraintInterface;
use Swiftly\Dependency\Type;

use function is_array;

/**
 * @psalm-immutable
 *
 * @upgrade:php8.2 Add #[Override] attribute
 * @upgrade:php8.2 Mark class as readonly
 *
 * @implements ConstraintInterface<array>
 */
final class ArrayConstraint implements ConstraintInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return Type::TYPE_ARRAY;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(mixed $value): bool
    {
        return is_array($value);
    }

    /**
     * @inheritDoc
     */
    public function isClassType(): bool
    {
        return false;
    }
}
