<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\Invokable;
use Swiftly\Dependency\LoaderInterface;
use Swiftly\Dependency\Service;

use function is_string;
use function function_exists;

/**
 * Container used to manage application services
 *
 * @author clvarley
 */
Class Container
{

    /**
     * Holds the currently registered services
     *
     * @var Service[] $services Registered services
     */
    protected $services = [];

    /**
     * Loads dependencies from the given loader
     *
     * @param LoaderInterface $loader Dependency loader
     * @return self                   Allow chaining
     */
    public function load( LoaderInterface $loader ) : Container
    {
        return $loader->load( $this );
    }

    /**
     * Binds a new service by name
     *
     * @param string $name   Service name
     * @param mixed $service Service provider
     * @return Service       Allow chaining
     */
    public function bind( string $name, /* object */ $service ) : Service
    {
        $this->services[$name] = new Service( $service, $this );

        return $this->services[$name];
    }

    /**
     * Tries to resolve the given service
     *
     * @param string $name Service name
     * @return object|null Resolved service
     */
    public function resolve( string $name ) // : object
    {
        return ( isset( $this->services[$name] )
            ? $this->services[$name]->resolve()
            : null
        );
    }

    /**
     * Used to set an alias for a service
     *
     * Please do not rely on this method, it may be reworked/removed in a
     * future release. Instead use the {@see Service::alias} method.
     *
     * @internal
     * @param string $name     Service name
     * @param Service $service Service definition
     * @return Service         Allow chaining
     */
    public function alias( string $name, Service $service ) : Service
    {
        $this->services[$name] = $service;

        return $this->services[$name];
    }

    /**
     * Checks to see if a named service is available
     *
     * @param string $name Service name
     * @return bool        Service available?
     */
    public function has( string $name ) : bool
    {
        return isset( $this->services[$name] );
    }
}
