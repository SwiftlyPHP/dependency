<?php declare(strict_types=1);

namespace Swiftly\Dependency\Type;

use Swiftly\Dependency\ConstraintInterface;

/**
 * @psalm-immutable
 *
 * @upgrade:php8.2 Add #[Override] attribute
 * @upgrade:php8.2 Mark class as readonly
 *
 * @template T
 *
 * @implements ConstraintInterface<T>
 */
final class ClassConstraint implements ConstraintInterface
{
    /**
     * @param class-string<T> $className
     */
    public function __construct(
        private readonly string $className,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->className;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(mixed $value): bool
    {
        return $value instanceof $this->className;
    }

    /**
     * @inheritDoc
     */
    public function isClassType(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
