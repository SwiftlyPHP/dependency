<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Closure;
use Swiftly\Dependency\Constraint\MixedConstraint;

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
     * @param null|callable():T $defaultValueCallback
     * @param list<ResolvableInterface<T>> $attributes
     */
    public function __construct(
        public readonly string $name,
        public readonly ConstraintInterface $type,
        public readonly ?Closure $defaultValueCallback,
        public readonly array $attributes,
    ) {
    }
}
