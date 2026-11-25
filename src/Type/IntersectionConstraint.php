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
     * @param non-empty-list<ConstraintInterface<T>> $members
     */
    public function __construct(
        private array $members,
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
    public function getName(): string
    {
        $parts = [];

        foreach ($this->members as $member) {
            $parts[] = $member instanceof CompoundConstraintInterface
                ? "({$member->getName()})"
                : $member->getName();
        }

        return implode('&', $parts);
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
    public function isClassType(): bool
    {
        foreach ($this->members as $member) {
            if ($member->isClassType()) {
                return true;
            }
        }

        return false;
    }
}
