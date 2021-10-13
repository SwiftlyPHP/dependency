<?php

namespace Swiftly\Dependency;

use Closure;
use ReflectionMethod;

use function is_string;
use function is_object;
use function is_array;

/**
 * Used to represent the types a callable can be
 *
 * When we get to PHP 8.1 we can swap to enums, but until then, this class will
 * have to do!
 *
 * @author clvarley
 */
Class Types
{

    /**
     * Indicates the callable is of an unknown type
     *
     * @var int TYPE_UNKNOWN Invalid callable
     */
    const TYPE_UNKNOWN = 0;

    /**
     * Indicates the callable is a standard function
     *
     * @var int TYPE_FUNCTION Standard function
     */
    const TYPE_FUNCTION = 1;

    /**
     * Indicates the callable is a closure
     *
     * @var int TYPE_CLOSURE Closure function
     */
    const TYPE_CLOSURE = 2;

    /**
     * Indicates the callable is a static method
     *
     * @var int TYPE_STATIC Static function
     */
    const TYPE_STATIC = 3;

    /**
     * Indicates the callable is a class method
     *
     * @var int TYPE_METHOD Class method
     */
    const TYPE_METHOD = 4;

    /**
     * Indicates the callable is an invokable object
     *
     * @var int TYPE_OBJECT Invokable object
     */
    const TYPE_OBJECT = 5;

    /**
     * Used to represent the special case of a class constructor
     *
     * @internal
     * @var int _CONSTRUCT Class constructor
     */
    const _CONSTRUCT = 6;

    /**
     * Attempt to infer exactly what type the given callable is
     *
     * @template TCall
     * @psalm-param TCall $callable
     * @psalm-return (
     *    TCall is Closure
     *    ? self::TYPE_CLOSURE
     *    : TCall is callable-string
     *    ? self::TYPE_FUNCTION
     *    : TCall is object
     *    ? self::TYPE_OBJECT
     *    : TCall is array{0:string,1:string}
     *    ? self::TYPE_STATIC
     *    : TCall is callable-array
     *    ? self::TYPE_METHOD
     *    : self::TYPE_UNKNOWN
     * )
     *
     * @param callable $callable Callable variable
     * @return int               Inferred type
     */
    public static function inferType( $callable ) : int
    {
        $type = self::TYPE_UNKNOWN;

        if ( $callable instanceof Closure ) {
            $type = self::TYPE_CLOSURE;
            return $type;
        }

        // A string? Must be a function
        if ( is_string( $callable ) ) {
            $type = Types::TYPE_FUNCTION;

        // Invokable object
        } else if ( is_object( $callable ) ) {
            $type = Types::TYPE_OBJECT;

        // Class method
        } else if ( is_array( $callable ) ) {
            $type = (( new ReflectionMethod( ...$callable ))->isStatic()
                ? Types::TYPE_STATIC
                : Types::TYPE_METHOD
            );
        }

        return $type;
    }
}
