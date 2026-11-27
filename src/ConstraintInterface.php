<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Swiftly\Dependency\TypeInterface;

/**
 * @template T
 *
 * @extends TypeInterface<T>
 */
interface ConstraintInterface extends TypeInterface
{
    /**
     * Determine whether this constraint is for a class, interface or enum type.
     *
     * @pure
     *
     * @psalm-assert-if-true class-string $this->getName()
     */
    public function isClassType(): bool;

    /**
     * Return the fully-qualified name of this type.
     *
     * @pure
     *
     * @return non-empty-string
     */
    public function getName(): string;
}
