<?php

namespace Swiftly\Dependency;

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
     * Maps to one of the TYPE_* constants.
     *
     * @var int $type Invokable type
     */
    private $type = TYPE_UNKNOWN;

    /**
     * The underlying callable function/method
     *
     * @var callable $callable Callable variable
     */
    private $callable;

    /**
     * Create a wrapper around the given callable variable
     *
     * @param callable $callable Callable variable
     */
    public function __construct( callable $callable )
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
        $type = $this->getType();

        // Should be impossible?
        if ( $type === self::TYPE_UNKNOWN ) {
            return [];
        }

        // Get appropriate reflection type
        switch ( $type ) {
            case self::TYPE_FUNCTION:
            case self::TYPE_CLOSURE:
                $func = new \ReflectionFunction( $this->callable );
                break;
            case self::TYPE_STATIC:
            case self::TYPE_METHOD:
                $func = new \ReflectionMethod( ...$this->callable );
                break;
            case self::TYPE_OBJECT:
                $func = new \ReflectionMethod( $this->callable, '__invoke' );
                break;
            case self::TYPE_CONSTRUCT:
                $func = (new \ReflectionClass( $this->callable[0] ))->getConstructor();
                break;
        }

        return ( $func !== null
            ? $func->getParameters()
            : []
        );
    }

    /**
     * Gets the type of the underlying callable
     *
     * Attempts to infer what kind of callable we are dealing with and returns
     * one of the TYPE_* constants.
     *
     * @return int Invokable type
     */
    public function getType() : int
    {
        // Type already inferred!
        if ( $this->type !== self::TYPE_UNKNOWN ) {
            return $this->type;
        }

        // Not even close to being callable!
        if ( !\is_callable( $this->callable, true ) ) {
            return $this->type;
        }

        if ( $this->callable instanceof \Closure ) {
            $this->type = self::TYPE_CLOSURE;
            return $this->type;
        }

        // Is object and is callable, so must have __invoke method
        if ( \is_object( $this->callable ) ) {
            $this->type = self::TYPE_OBJECT;
            return $this->type;
        }

        // Possibly using the "class::method" string format?
        if ( \is_string( $this->callable ) ) {
            if ( !\strpos( $this->callable, '::' ) ) {
                $this->type = self::TYPE_FUNCTION;
                return $this->type;
            }

            $this->callable = \explode( '::', $this->callable );
        }

        // Handle special internal case of constructor
        if ( $this->callable[1] === '__construct' ) {
            $this->type = self::TYPE_CONSTRUCT;
            return $this->type;
        }

        try {
            $reflected = new \ReflectionMethod( ...$this->callable );
        } catch ( \ReflectionException $e ) {
            return $this->type; // Method doesn't exist!
        }

        $this->type = ( $reflected->isStatic()
            ? self::TYPE_STATIC
            : self::TYPE_METHOD
        );

        return $this->type;
    }

    /**
     * Invoke the underlying callable and return its result
     *
     * @param array $arguments (Optional) Callable arguments
     */
    public function invoke( array $arguments = [] ) // : mixed
    {
        $type = $this->getType();

        switch ( $type ) {
            case self::TYPE_FUNCTION:
            case self::TYPE_CLOSURE:
            case self::TYPE_OBJECT:
                $result = \call_user_func_array( $this->callable, $arguments );
                break;
            case self::TYPE_STATIC:
            case self::TYPE_METHOD:
                $result = \call_user_func_array( $this->callable, $arguments );
                break;
            case self::TYPE_CONSTRUCT:
                $reflect = new \ReflectionClass( $this->callable[0] );
                $result = $reflect->newInstanceArgs( $arguments );
            break;
        }

        return $result;
    }
}
