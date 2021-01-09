<?php

namespace Swiftly\Dependency\Loader;

use Swiftly\Dependency\{
    Container,
    LoaderInterface
};

/**
 *
 */
Class PhpLoader Implements LoaderInterface
{

    /**
     * Attempts to load the dependencies from PHP file
     *
     * @param Container $container Dependency container
     * @return void                N/a
     */
    public function load( Container $container ) : Container
    {
        // TODO: Load stuff!

        return $container;
    }
}
