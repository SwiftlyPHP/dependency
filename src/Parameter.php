<?php

namespace Swiftly\Dependency;

use function is_object;
use function gettype;
use function is_numeric;
use function in_array;

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
        $this->type = ($type ? self::mapType($type) : $type);
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
        $type = self::mapType($type);

        switch($type) {
            case 'bool':
            case 'array':
                return $type === $this->type;
            case 'int':
            case 'float':
                return $strict
                    ? $this->type === $type
                    : $this->isNumeric();
            case 'string':
                return $strict
                    ? $this->type === $type
                    : ($this->type === $type
                        || ($this->isNumeric() && is_numeric($value)));
            default:
                return false;
        }
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
     * Maps some of the older longhand type names to their newer variants
     *
     * @php:8.0 Swap to using match statement
     * @param string $type Datatype name
     * @return string      Datatype name
     */
    private static function mapType(string $type): string
    {
        return [
            'integer' => 'int',
            'double'  => 'float',
            'boolean' => 'bool'
        ][$type] ?? $type;
    }
}
