<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\Types;

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
 * @template TFunc as callable
 *
 * @author clvarley
 */
Class Invokable
{

    /**
     * The type of this callable
     *
     * @psalm-var Types::TYPE_* $type
     *
     * @var int $type Callable type
     */
    private $type = Types::TYPE_UNKNOWN;

    /**
     * The reflected function or method
     *
     * @var ReflectionFunctionAbstract|null $reflected Reflected function/method
     */
    private $reflected = null;

    /**
     * The underlying callable function/method
     *
     * @psalm-var TFunc $callable
     *
     * @var callable $callable Callable variable
     */
    private $callable;

    /**
     * Handles the special case of class constructors
     *
     * @template C
     * @psalm-param class-string<C> $class
     *
     * @param string $class Classname
     * @return Invokable    Constructor invokable
     */
    public static function forConstructor( string $class ) : Invokable
    {
        $reflected = new ReflectionClass( $class );

        /** @psalm-var callable():C $callback */
        $callback = [ $reflected, 'newInstanceArgs' ];

        $invokable = new Invokable( $callback );
        $invokable->type = Types::TYPE_METHOD;
        $invokable->reflected = $reflected->getConstructor();

        return $invokable;
    }

    /**
     * Create a wrapper around the given callable variable
     *
     * Have to avoid use of the `callable` typehint to suppress the:
     * "Non-static method should not be called statically" warning
     *
     * @psalm-param TFunc $callable
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
     * @psalm-return list<ReflectionParameter>
     *
     * @return ReflectionParameter[] Defined parameters
     */
    public function getParameters() : array
    {
        $reflection = $this->getReflection();

        return $reflection ? $reflection->getParameters() : [];
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
            case Types::TYPE_FUNCTION:
            case Types::TYPE_CLOSURE:
                $this->reflected = new ReflectionFunction( $this->callable );
                break;
            case Types::TYPE_STATIC:
            case Types::TYPE_METHOD:
                $this->reflected = new ReflectionMethod( ...$this->callable );
                break;
            case Types::TYPE_OBJECT:
                $this->reflected = new ReflectionMethod( $this->callable, '__invoke' );
                break;
            case Types::TYPE_UNKNOWN:
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
     * @psalm-return Types::TYPE_*
     *
     * @return int Callable type
     */
    public function getType() : int
    {
        // Yet to infer type
        if ( $this->type === Types::TYPE_UNKNOWN ) {
            $this->type = Types::inferType( $this->callable );
        }

        return $this->type;
    }

    /**
     * Invoke the underlying function and return its result
     *
     * @psalm-param list<mixed> $arguments
     *
     * @param mixed[] $arguments Function arguments
     * @return mixed             Function result
     */
    public function invoke( array $arguments = [] ) // : mixed
    {
        switch ( $this->getType() ) {
            case Types::TYPE_FUNCTION:
            case Types::TYPE_CLOSURE:
            case Types::TYPE_OBJECT:
                return ($this->callable)( ...$arguments );
                break;
            case Types::TYPE_STATIC:
            case Types::TYPE_METHOD:
                return call_user_func_array( $this->callable, $arguments );
                break;
        }
    }
}
