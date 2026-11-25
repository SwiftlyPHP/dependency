<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Swiftly\Dependency\ConstraintInterface;

/**
 * @see \Swiftly\Dependency\Type\IntersectionConstraint
 * @see \Swiftly\Dependency\Type\UnionConstraint
 *
 * @template T
 *
 * @extends ConstraintInterface<T>
 */
interface CompoundConstraintInterface extends ConstraintInterface
{
    /**
     * Return all members of this union or intersection type.
     *
     * @return non-empty-list<ConstraintInterface<T>>
     */
    public function getMembers(): array;
}
