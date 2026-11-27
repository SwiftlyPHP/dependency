<?php declare(strict_types=1);

namespace Swiftly\Dependency\Type;

use Swiftly\Dependency\CompoundConstraintInterface;
use Swiftly\Dependency\ConstraintInterface;

use function implode;

/**
 * @psalm-immutable
 *
 * @upgrade:php8.2 Add #[Override] attribute
 * @upgrade:php8.2 Mark class as readonly
 *
 * @template T
 *
 * @implements CompoundConstraintInterface<T>
 */
final class IntersectionConstraint implements CompoundConstraintInterface
{
    /**
     * @param non-empty-list<ConstraintInterface<T>|CompoundConstraintInterface<T>> $members
     */
    public function __construct(
        private readonly array $members,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(mixed $value): bool
    {
        foreach ($this->members as $member) {
            if (false === $member->isSatisfiedBy($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        $parts = [];

        foreach ($this->members as $member) {
            $parts[] = $member instanceof CompoundConstraintInterface
                ? "({$member})"
                : (string) $member;
        }

        return implode('&', $parts);
    }
}
