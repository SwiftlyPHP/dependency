<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\Container;
use Swiftly\Dependency\Invokable;
use Swiftly\Dependency\Types;
use Swiftly\Dependency\Exception\UnionTypeException;
use ReflectionParameter;
use ReflectionNamedType;

use function array_merge;
use function is_object;
use function is_string;
use function is_callable;

/**
 * Class used to represent an application service
 *
 * @template T of object
 * @author clvarley
 */
Class Service
{

    /**
     * The callback used to create this service
     *
     * @psalm-var class-string<T>|callable():T|T $callback
     *
     * @var string|callable|object $callable Service callback
     */
    protected $callback;

    /**
     * The container in which this service is registered
     *
     * @var Container $container Service container
     */
    protected $container;

    /**
     * The resolved service object
     *
     * @psalm-var T|null $resolved
     *
     * @var object|null $resolved Resolved service
     */
    protected $resolved = null;

    /**
     * Parameters to be used during service creation
     *
     * @psalm-var array<string,mixed> $parameters
     *
     * @var array $parameters Service parameters
     */
    protected $parameters = [];

    /**
     * Marks whether or not this service is a singleton
     *
     * @var bool $singleton Single service?
     */
    protected $singleton = true;

    /**
     * Post initialization hooks to be run at resolution
     *
     * @var Invokable[] $hooks Post-initialization hooks
     */
    protected $hooks = [];

    /**
     * Construct a service wrapper around the given function/method/object
     *
     * @psalm-param class-string<T>|callable():T|T $callback
     *
     * @param string|callable|object $callback Service callback
     * @param Container $container             Dependency container
     */
    public function __construct(
        /*string|callable*/ $callback,
        Container $container
    ) {
        $this->callback = $callback;
        $this->container = $container;
    }

    /**
     * Sets an alias for this service
     *
     * @param string $name Service name
     * @return self        Allow chaining
     */
    public function alias( string $name ) : self
    {
        $this->container->alias( $name, $this );

        return $this;
    }

    /**
     * Sets if this service should be a singleton
     *
     * @param bool $singleton Single service?
     * @return self           Allow chaining
     */
    public function singleton( bool $singleton ) : self
    {
        $this->singleton = $singleton;

        return $this;
    }

    /**
     * Sets parameters to be used during service creation
     *
     * @psalm-param array<string,mixed> $parameters
     *
     * @param mixed[] $parameters Service parameters
     * @return self               Allow chaining
     */
    public function parameters( array $parameters ) : self
    {
        $this->parameters = array_merge( $this->parameters, $parameters );

        return $this;
    }

    /**
     * Sets a callback to be run post-resolution
     *
     * @param callable $hook Post-initialization hook
     * @return self          Allow chaining
     */
    public function then( callable $hook ) : self
    {
        $this->hooks[] = new Invokable( $hook );

        return $this;
    }

    /**
     * Resolve the service and returns the instantiated object
     *
     * @psalm-return T
     *
     * @return object Instantiated object
     */
    public function resolve() // : object
    {
        if ( $this->resolved !== null ) {
            return $this->resolved;
        }

        // Might be a regular object?
        if ( is_object( $this->callback )
            && !method_exists( $this->callback, '__invoke' )
        ) {
            return $this->callback;
        }

        // Maybe just a class name?
        if ( !is_callable( $this->callback ) && is_string( $this->callback ) ) {
            $callback = Invokable::forConstructor( $this->callback );
        } else {
            $callback = new Invokable( $this->callback );
        }

        // We need to instantiate the object first!
        if ( $callback->getType() === Types::TYPE_METHOD
            && !is_object( $this->callback[0] )
        ) {
            $object = Invokable::forConstructor( $this->callback[0] );
            $object = $this->invoke( $object );

            // Update ref so we don't have to do this again
            $this->callback[0] = $object;
            $callback = new Invokable( $this->callback );
        }

        // Resolve the object
        /** @psalm-var T $this->resolved */
        $this->resolved = $this->invoke( $callback );

        // Run any post hooks!
        foreach ( $this->hooks as $hook ) {
            $this->invoke( $hook );
        }

        // Get the result!
        return $this->resolved;
    }

    /**
     * Resolves any neccessary parameters and invokes the action
     *
     * @param Invokable $action Invokable action
     * @return mixed            Action result
     */
    private function invoke( Invokable $action ) // : mixed
    {
        $arguments = [];

        foreach ( $action->getParameters() as $parameter ) {
            $arguments[] = $this->param( $parameter );
        }

        return $action->invoke( $arguments );
    }

    /**
     * Attempts to resolve a parameter value
     *
     * @param ReflectionParameter $parameter Reflected parameter
     * @return mixed                         Parameter value
     */
    private function param( ReflectionParameter $parameter ) // : mixed
    {
        $name = $parameter->getName();

        if ( isset( $this->parameters[$name] ) ) {
            return $this->parameters[$name];
        }

        $type = $parameter->getType();

        // We can't really support PHP8 union types (yet)!
        if ( $type && $type instanceof ReflectionNamedType === false ) {
            throw new UnionTypeException(); // TODO
        }

        // If no type supplied?
        if ( $type === null || $type->isBuiltin() ) {
            if ( $parameter->isDefaultValueAvailable() ) {
                $value = $parameter->getDefaultValue();
            } else {
                $value = null;
            }

            return $value;
        }

        /** @psalm-var class-string $type */
        $type = $type->getName();

        return $this->container->resolve( $type );
    }
}
