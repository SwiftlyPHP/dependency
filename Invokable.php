<?php

namespace Swiftly\Dependency;

use Closure;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionClass;
use ReflectionParameter;
use ReflectionFunctionAbstract;

use function is_string;
use function strpos;
use function explode;
use function is_object;
use function call_user_func_array;

/**
 * Class used to represent a callable function/method
 *
 * @author clvarley
 */
Class Invokable
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
     * Indicates the callable is actually a class constructor
     *
     * @var int TYPE_CONSTRUCT Class constructor
     */
    const TYPE_CONSTRUCT = 6;

    /**
     * The type of this callable
     *
     * @var int $type Invokable type
     */
    private $type = self::TYPE_UNKNOWN;

    /**
     * The reflected function or method
     *
     * @var ReflectionFunctionAbstract $reflected Reflected function/method
     */
    private $reflected = null;

    /**
     * The underlying callable function/method
     *
     * @var callable $callable Callable variable
     */
    private $callable;

    /**
     * Handles the special case of class constructors
     *
     * @param object|string $class Class(name)
     * @return Invokable           Constructor invokable
     */
    public static function forConstructor( /* object|string */ $class ) : Invokable
    {
        $reflected = new ReflectionClass( $class );

        $invokable = new Invokable([ $reflected, 'newInstanceArgs' ]);
        $invokable->type = Invokable::TYPE_CONSTRUCT;
        $invokable->reflected = $reflected->getConstructor();

        return $invokable;
    }

    /**
     * Create a wrapper around the given callable variable
     *
     * Have to avoid use of the `callable` typehint to suppress the:
     * "Non-static method should not be called statically" warning
     *
     * @param callable $callable Callable variable
     */
    public function __construct( /* callable */ $callable )
    {
        $this->callable = $callable;
    }

    /**
     * Gets the parameters expected by this callable
     *
     * @return ReflectionParameter[] Defined parameters
     */
    public function getParameters() : array
    {
        if ( $this->reflected === null && $this->type === self::TYPE_CONSTRUCT ) {
            return []; // Class with no constructor
        }

        return $this->getReflection()->getParameters();
    }

    /**
     * Gets the ReflectionFunction/ReflectionMethod for this callable
     *
     * @return ReflectionFunctionAbstract|null Reflection object
     */
    public function getReflection() : ?ReflectionFunctionAbstract
    {
        // Already reflected
        if ( $this->reflected !== null ) {
            return $this->reflected;
        }

        // Choose appropriate representation
        switch ( $this->getType() ) {
            case self::TYPE_FUNCTION:
            case self::TYPE_CLOSURE:
                $this->reflected = new ReflectionFunction( $this->callable );
                break;
            case self::TYPE_STATIC:
            case self::TYPE_METHOD:
                $this->reflected = new ReflectionMethod( ...$this->callable );
                break;
            case self::TYPE_OBJECT:
                $this->reflected = new ReflectionMethod( $this->callable, '__invoke' );
                break;
            case self::TYPE_UNKNOWN:
            case self::TYPE_CONSTRUCT:
                // Throw: Custom exception
                break;
        }

        return $this->reflected;
    }

    /**
     * Gets the type of this callable
     *
     * Returns one of the invokable `TYPE_*` constants.
     *
     * @return int
     */
    public function getType() : int
    {
        // Already inferred type
        if ( $this->type !== self::TYPE_UNKNOWN ) {
            return $this->type;
        }

        $callable = $this->callable;

        if ( $callable instanceof Closure ) {
            $this->type = self::TYPE_CLOSURE;
            return $this->type;
        }

        // Support older "Class::method" syntax?
        if ( is_string( $callable ) && strpos( $callable, '::' ) ) {
            $callable = explode( '::', $callable );
        }

        // Still a string? Must be a function
        if ( is_string( $callable ) ) {
            $this->type = self::TYPE_FUNCTION;

        // Invokable object
        } else if ( is_object( $callable ) ) {
            $this->type = self::TYPE_OBJECT;

        // Class method
        } else {
            $this->type = (( new ReflectionMethod( ...$callable ))->isStatic()
                ? self::TYPE_STATIC
                : self::TYPE_METHOD
            );
        }

        return $this->type;
    }

    /**
     * Invoke the underlying function and return its result
     *
     * @param mixed[] $arguments Function arguments
     * @return mixed             Function result
     */
    public function invoke( array $arguments = [] ) // : mixed
    {
        switch ( $this->type ) {
            case self::TYPE_FUNCTION:
            case self::TYPE_CLOSURE:
            case self::TYPE_OBJECT:
                return ($this->callable)( ...$arguments );
            break;
            case self::TYPE_STATIC:
            case self::TYPE_METHOD:
                return call_user_func_array( $this->callable, $arguments );
            break;
            case self::TYPE_CONSTRUCT:
                return ($this->callable)( $arguments );
            break;
        }
    }
}
