<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\{
    Container,
    Invokable
};

/**
 * Class used to represent an application service
 *
 * @author clvarley
 */
Class Service
{

    /**
     * The container in which this service is registered
     *
     * @var Container $container Service container
     */
    protected $container;

    /**
     * The callback used to create this service
     *
     * @var Invokable $callable Service callback
     */
    protected $callback;

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
        $this->parameters = \array_merge( $this->parameters, $parameters );

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

        // TODO: implement!
    }
}
