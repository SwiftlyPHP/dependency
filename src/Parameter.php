<?php declare(strict_types=1);

namespace Swiftly\Dependency;

/**
 * @upgrade:php8.2 Mark class as readonly
 *
 * @psalm-immutable
 *
 * @template T
 */
final class Parameter
{
    /**
     * @param non-empty-string $name
     * @param ConstraintInterface<T>|CompoundConstraintInterface<T> $type
     */
    public function __construct(
        public readonly string $name,
        public readonly ConstraintInterface|CompoundConstraintInterface $type,
        public readonly bool $hasDefaultValue,
        public readonly bool $isNullable,
    ) {
    }
}
