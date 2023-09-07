<?php

namespace Swiftly\Dependency\Parameter;

use Swiftly\Dependency\Parameter;

use function is_numeric;

/**
 * Function parameter that expects either an int or float value
 *
 * This class exists - over having seperate int and float variants - to mirror
 * the behaviour of PHP's type coercion in non-strict mode. Any function
 * expecting an int can recieve a float and vice-versa.
 *
 * @api
 * @extends Parameter<int|float>
 */
class NumericParameter extends Parameter
{
    /** @var "int"|"float" $subtype */
    private string $subtype;

    /**
     * Create a new NumericParameter instance, specifying the underlying type
     *
     * @psalm-param null|callable():(int|float) $default
     * @param non-empty-string $name Parameter name
     * @param "int"|"float" $subtype Numeric type
     * @param bool $is_nullable      Parameter allows null values?
     * @param null|callable $default Default value provider function   
     */
    public function __construct(
        string $name,
        string $subtype,
        bool $is_nullable,
        $default = null
    ) {
        $this->subtype = $subtype;

        parent::__construct($name, $is_nullable, $default);
    }

    /** {@inheritDoc} */
    public function getType(): string
    {
        return $this->subtype;
    }

    /** {@inheritDoc} */
    public function isBuiltin(): bool
    {
        return true;
    }

    /** {@inheritDoc} */
    public function accepts($subject): bool
    {
        return is_numeric($subject);
    }
}
