<?php declare(strict_types=1);

namespace Swiftly\Dependency\Type;

use Swiftly\Dependency\ConstraintInterface;
use Swiftly\Dependency\Type;

use function is_int;

/**
 * @psalm-immutable
 *
 * @upgrade:php8.2 Add #[Override] attribute
 * @upgrade:php8.2 Mark class as readonly
 *
 * @implements ConstraintInterface<int>
 */
final class IntConstraint implements ConstraintInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return Type::TYPE_INT;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(mixed $value): bool
    {
        return is_int($value);
    }

    /**
     * @inheritDoc
     */
    public function isClassType(): bool
    {
        return false;
    }
}
