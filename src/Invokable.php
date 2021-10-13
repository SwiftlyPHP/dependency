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
 * @template TVal
 *
 * @author clvarley
 */
Class Invokable
{

    /**
     * The type of this callable
     *
     * @psalm-var Types::_CONSTRUCT|Types::TYPE_* $type
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
     * @psalm-var callable(mixed=):TVal $callable
     *
     * @var callable $callable Callable variable
     */
    private $callable;

    /**
     * Handles the special case of class constructors
     *
     * @template C
     * @psalm-param class-string<C> $class
     * @psalm-return Invokable<C>
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
        $invokable->type = Types::_CONSTRUCT;
        $invokable->reflected = $reflected->getConstructor();

        return $invokable;
    }

    /**
     * Create a wrapper around the given callable variable
     *
     * Have to avoid use of the `callable` typehint to suppress the:
     * "Non-static method should not be called statically" warning
     *
     * @psalm-param callable():TVal $callable
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
        if ( $this->reflected !== null || $this->type === Types::_CONSTRUCT ) {
            return $this->reflected;
        }

        // Choose appropriate representation
        switch ( $this->getType() ) {
            case Types::TYPE_FUNCTION:
            case Types::TYPE_CLOSURE:
                /** @psalm-var Closure|callable-string $this->callable */
                $this->reflected = new ReflectionFunction( $this->callable );
                break;
            case Types::TYPE_STATIC:
            case Types::TYPE_METHOD:
                /** @psalm-var array{0:class-string,1:string} $this->callable */
                $this->reflected = new ReflectionMethod( ...$this->callable );
                break;
            case Types::TYPE_OBJECT:
                /** @psalm-var callable-object $this->callable */
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
     * @psalm-return TVal
     *
     * @param mixed[] $arguments Function arguments
     * @return mixed             Function result
     */
    public function invoke( array $arguments = [] ) // : mixed
    {
        if (( $type = $this->getType() )=== Types::TYPE_UNKNOWN ) {
            // TODO: Throw
        }

        // Handle constructor edge case
        if ( $type === Types::_CONSTRUCT ) {
            $arguments = [$arguments];
        }

        // Let's go!
        return ($this->callable)( ...$arguments );
    }
}
