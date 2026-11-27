<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Stringable;

/**
 * Represents a type hint for a function parameter.
 *
 * @psalm-immutable
 *
 * @template T
 */
interface TypeInterface extends Stringable
{
    /**
     * Determine whether the given `$value` satisfies this type constraint.
     *
     * @psalm-assert-if-true T $value
     */
    public function isSatisfiedBy(mixed $value): bool;
}
