<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\{
    LoaderInterface,
    Service
};

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
        $this->services[$name] = new Service( $service );

        return $this->services[$name];
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
     * @return bool Service available?
     */
    public function has() : bool
    {
        return isset( $this->services[$name] );
    }
}
