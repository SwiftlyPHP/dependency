<?php

namespace Swiftly\Dependency;

use function is_object;
use function gettype;
use function in_array;
use function is_numeric;

/**
 * Holds information regarding a single function/method parameter
 *
 * @internal
 * @readonly
 * @template T
 */
final class Parameter
{
    /** Parameter name */
    public string $name;

    /** Parameter datatype */
    public ?string $type;

    /** @var T|null $default Default parameter value */
    public $default;

    /** Is native datatype */
    public bool $builtin;

    /**
     * Create a new parameter definition
     * 
     * @param string $name    Parameter name
     * @param ?string $type   Parameter data type
     * @param T|null $default Parameter default value
     * @param bool $builtin   Is native datatype
     */
    public function __construct(
        string $name,
        ?string $type,
        $default,
        bool $builtin
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->default = $default;
        $this->builtin = $builtin;
    }

    /**
     * Determine if a given value would satisfy this parameter
     *
     * @psalm-assert-if-true T $value
     *
     * @param mixed $value Subject parameter value
     * @param bool $strict Use strict type comparison
     * @return bool        Satisfies parameter
     */
    public function validate($value, bool $strict = false): bool
    {
        if (is_object($value)) {
            return ($value instanceof $this->type);
        }

        $type = gettype($value);

        switch($type) {
            case 'boolean':
            case 'array':
                return $type === $this->type;
            case 'integer':
            case 'double':
                return $strict ? $this->isType($type) : $this->isNumeric();
            case 'string':
                return $strict
                    ? $this->isType($type)
                    : $this->wouldAllowString($value);
            default:
                return false;
        }
    }
    
    /**
     * Determine if this parameter is a certain type
     *
     * @param string $type Data type
     * @return bool        Is datatype
     */
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Determine if this parameter is numeric
     *
     * @return bool Parameter is numeric
     */
    public function isNumeric(): bool 
    {
        return in_array($this->type, ['int', 'float'], true);
    }

    /**
     * Determine if the subject string could be used in place of an int or float
     * 
     * @param string $subject Subject string
     * @return bool           Allowed numeric string
     */
    public function wouldAllowString(string $subject): bool
    {
        if (!$this->isNumeric()) {
            return false;
        }

        return is_numeric($subject);
    }
}
