<?php

namespace Swiftly\Dependency\Parameter;

use Swiftly\Dependency\Parameter;

use function is_object;

/**
 * Function parameter that expects an object of a given type/interface
 *
 * @api
 * @psalm-immutable
 * @template T of object
 * @extends Parameter<T>
 */
class NamedClassParameter extends Parameter
{
    /** @var class-string<T> $type */
    private string $type;

    /**
     * Create a new parameter constrained to the given class/interface
     *
     * @psalm-param null|callable():T $default
     * @param non-empty-string $name Parameter name
     * @param class-string<T> $type  Fully qualified class/interface name
     * @param bool $is_nullable      Parameter allows null values?
     * @param null|callable $default Default value provider function
     */
    public function __construct(
        string $name,
        string $type,
        bool $is_nullable,
        $default = null
    ) {
        $this->type = $type;

        parent::__construct($name, $is_nullable, $default);
    }

    /**
     * {@inheritDoc}
     *
     * @return class-string<T> Fully qualified class/interface name
     */
    public function getType(): string
    {
        return $this->type;
    }

    /** {@inheritDoc} */
    public function isBuiltin(): bool
    {
        return false;
    }

    /** {@inheritDoc} */
    public function accepts($subject): bool
    {
        return (
            (is_object($subject) && $subject instanceof $this->type)
            || ($this->isNullable() && $subject === null)
        );
    }
}
