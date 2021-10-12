<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\LoaderInterface;
use Swiftly\Dependency\Service;
use Swiftly\Dependency\Exception\NotFoundException;
use Swiftly\Dependency\Exception\UnexpectedTypeException;

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
     * @psalm-var array<class-string,Service> $services
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
     * @template T of object
     * @psalm-param class-string<T> $name
     * @psalm-param class-string<T>|callable():T|T $service
     * @psalm-return Service<T>
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
     * @template T of object
     * @psalm-param class-string<T> $name
     * @psalm-return T
     *
     * @throws NotFoundException
     * @param string $name Service name
     * @return object      Resolved service
     */
    public function resolve( string $name ) // : object
    {
        if ( !$this->has( $name ) ) {
            throw new NotFoundException(); // TODO:
        }

        $resolved = $this->services[$name]->resolve();

        // Resolution returned wrong type
        if ( $resolved instanceof $name === false ) {
            throw new UnexpectedTypeException( $name, $resolved );
        }

        return $resolved;
    }

    /**
     * Used to set an alias for a service
     *
     * Please do not rely on this method, it may be reworked/removed in a
     * future release. Instead use the {@see Service::alias} method.
     *
     * @psalm-param class-string $name
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
     * @psalm-param class-string $name
     *
     * @param string $name Service name
     * @return bool        Service available?
     */
    public function has( string $name ) : bool
    {
        return isset( $this->services[$name] );
    }
}
