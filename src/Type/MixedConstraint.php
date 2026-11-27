<?php declare(strict_types=1);

namespace Swiftly\Dependency\Type;

use Swiftly\Dependency\ConstraintInterface;
use Swiftly\Dependency\Type;

/**
 * @psalm-immutable
 *
 * @upgrade:php8.2 Add #[Override] attribute
 * @upgrade:php8.2 Mark class as readonly
 *
 * @implements ConstraintInterface<mixed>
 */
final class MixedConstraint implements ConstraintInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return Type::TYPE_MIXED;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(mixed $value): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isClassType(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
