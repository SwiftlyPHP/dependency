<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\Container;

/**
 * Interface for classes that can load services
 *
 * @author clvarley
 */
Interface LoaderInterface
{

    /**
     * Load services into the given dependency container
     *
     * Loaders are expected to return the dependency container to allow method
     * chaining.
     *
     * @param Container $container Dependency container
     * @return Container           Updated container
     */
    public function load( Container $container ) : Container;

}
