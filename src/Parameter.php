<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Swiftly\Dependency\Exception\UndefinedDefaultValueException;

/**
 * Base class from which all parameter types inherit.
 *
 * @api
 * @psalm-immutable
 * @template T
 */
abstract class Parameter
{
    /** @var non-empty-string $name (case-sensitive) */
    protected string $name;

    /** Allows nullable arguments? */
    protected bool $is_nullable;

    /**
     * Declared default value for this parameter.
     *
     * The default value is now wrapped in a callable because (since PHP 8.1) it
     * is possible to construct an object using `new` in default parameters. By
     * hiding the default behind a callable we can lazily evaluate the value,
     * therefore delaying any potentially expensive initialization.
     *
     * @var null|callable():T $default
     */
    protected $default;

    /**
     * Create a new parameter definition.
     *
     * Extending classes are strongly encouraged to implement their own
     * constructors and then pass any neccessary values to
     * `parent::__construct`.
     *
     * @param non-empty-string $name Case-sensitive parameter name.
     * @param bool $is_nullable      Parameter allows null values?
     * @param null|callable $default Default value provider function.
     * @psalm-param null|callable():T $default
     */
    public function __construct(
        string $name,
        bool $is_nullable,
        $default = null
    ) {
        $this->name = $name;
        $this->is_nullable = $is_nullable;
        $this->default = $default;
    }

    /**
     * Return the name of this parameter.
     *
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Determine whether or not this parameter accepts null values.
     */
    public function isNullable(): bool
    {
        return $this->is_nullable;
    }

    /**
     * Determine whether or not this parameter has a default value.
     *
     * @psalm-assert-if-true !null $this->default
     */
    public function hasDefault(): bool
    {
        return $this->default !== null;
    }

    /**
     * Return a callback that resolves to the default value.
     *
     * @throws UndefinedDefaultValueException If no default value is available.
     *
     * @psalm-return callable():T
     */
    public function getDefaultCallback(): callable
    {
        if ($this->default === null) {
            throw new UndefinedDefaultValueException($this->name);
        }

        return $this->default;
    }

    /**
     * Return the datatype this parameter accepts.
     *
     * @return non-empty-string
     */
    abstract public function getType(): string;

    /**
     * Determine if this parameter accepts a native/non-compound datatype.
     *
     * The naming here is slightly ambiguous as this method is meant to resemble
     * the {@see \ReflectionNamedType::isBuiltin()} method on which it relies.
     * In the context of this library however, built-in refers to any parameter
     * that is not a user-defined datatype (which in essence means any
     * value that isn't a class or interface)
     *
     * @psalm-pure
     * @psalm-assert-if-false class-string<T> $this->getType()
     *
     * @return bool Accepts a built-in type?
     */
    abstract public function isBuiltin(): bool;

    /**
     * Determine if the given value would satisfy this parameter.
     *
     * @psalm-assert-if-true T $subject
     * @param mixed $subject
     */
    abstract public function accepts(mixed $subject): bool;
}
