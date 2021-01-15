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
     * Returns whether this loader can load the given file
     *
     * @param string $file File path
     * @return bool        File supported
     */
    public function supports( string $file ) : bool;

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
