<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\{
    Container,
    Invokable
};

use ReflectionParameter;

use function array_merge;

/**
 * Class used to represent an application service
 *
 * @author clvarley
 */
Class Service
{

    /**
     * The callback used to create this service
     *
     * @var Invokable $callable Service callback
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
     * @var object|null $resolved Resolved service
     */
    protected $resolved = null;

    /**
     * Parameters to be used during service creation
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
     *
     */
    public function __construct( Invokable $callback, Container $container )
    {
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
     * @internal
     * @return object|null Instantiated object
     */
    public function resolve() // : ?object
    {
        if ( $this->resolved !== null ) {
            return $this->resolved;
        }

        // Resolve the object
        $this->resolved = $this->invoke( $this->callback );

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
     * @return mixed|null                    Parameter value
     */
    private function param( ReflectionParameter $parameter ) // : mixed
    {
        $name = $parameter->getName();

        if ( isset( $this->parameters[$name] ) ) {
            return $this->parameters[$name];
        }

        $type = $parameter->getType();

        // If no type supplied?
        if ( $type === null || $type->isBuiltin() ) {
            if ( $parameter->isDefaultValueAvailable() ) {
                $value = $parameter->getDefaultValue();
            } else {
                $value = null;
            }

            return $value;
        }

        // Get the class/interface name
        $type = $type->getName();

        return $this->container->resolve( $type );
    }
}
