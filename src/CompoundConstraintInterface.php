<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Swiftly\Dependency\ConstraintInterface;
use Swiftly\Dependency\TypeInterface;

/**
 * @template T
 *
 * @extends TypeInterface<T>
 */
interface CompoundConstraintInterface extends TypeInterface
{
    /**
     * Return all members of this union or intersection type.
     *
     * @return non-empty-list<ConstraintInterface<T>|CompoundConstraintInterface<T>>
     */
    public function getMembers(): array;
}
