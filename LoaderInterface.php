<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\Container;

/**
 *
 */
Interface LoaderInterface
{

    /**
     * Attempts to load the dependencies from PHP file
     *
     * @param Container $container Dependency container
     * @return void                N/a
     */
    public function load( Container $container ) : Container;

}
