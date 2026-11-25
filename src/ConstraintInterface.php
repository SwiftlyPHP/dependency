<?php declare(strict_types=1);

namespace Swiftly\Dependency;

/**
 * @psalm-immutable
 *
 * @template T
 */
interface ConstraintInterface
{
    /**
     * Determine whether the given `$value` satisfies this type constraint.
     *
     * @psalm-assert-if-true T $value
     */
    public function isSatisfiedBy(mixed $value): bool;

    /**
     * Determine whether this constraint is for a class, interface or enum type.
     */
    public function isClassType(): bool;

    /**
     * Return the fully-qualified name of this type.
     *
     * @return non-empty-string
     */
    public function getName(): string;
}
