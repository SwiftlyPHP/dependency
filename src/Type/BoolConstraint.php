<?php declare(strict_types=1);

namespace Swiftly\Dependency\Type;

use Swiftly\Dependency\ConstraintInterface;
use Swiftly\Dependency\Type;

use function is_bool;

/**
 * @psalm-immutable
 *
 * @upgrade:php8.2 Add #[Override] attribute
 * @upgrade:php8.2 Mark class as readonly
 *
 * @implements ConstraintInterface<bool>
 */
final class BoolConstraint implements ConstraintInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return Type::TYPE_BOOL;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(mixed $value): bool
    {
        return is_bool($value);
    }

    /**
     * @inheritDoc
     */
    public function isClassType(): bool
    {
        return false;
    }
}
