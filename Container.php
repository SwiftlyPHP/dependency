<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\{
    LoaderInterface,
    Service
};

/**
 *
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
    public function load() : Container
    {
        return $loader->load( $this );
    }

    /**
     * 
     */
    public function bind( string $name, /* object */ $service ) : Service
    {
        // TODO:
    }
}
