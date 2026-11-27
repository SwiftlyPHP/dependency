<?php declare(strict_types=1);

namespace Swiftly\Dependency\Type;

use Swiftly\Dependency\ConstraintInterface;
use Swiftly\Dependency\Type;

use function is_string;

/**
 * @psalm-immutable
 *
 * @upgrade:php8.2 Add #[Override] attribute
 * @upgrade:php8.2 Mark class as readonly
 *
 * @implements ConstraintInterface<string>
 */
final class StringConstraint implements ConstraintInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return Type::TYPE_STRING;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(mixed $value): bool
    {
        return is_string($value);
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
