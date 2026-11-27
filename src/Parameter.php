<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Closure;

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
     * @param ConstraintInterface<T> $type
     */
    public function __construct(
        public readonly string $name,
        public readonly ConstraintInterface $type,
        public readonly bool $hasDefaultValue,
    ) {
    }
}
