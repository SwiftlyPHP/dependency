<?php

namespace Swiftly\Dependency\Exception;

use RuntimeException;

use function sprintf;

/**
 * Exception thrown if a class cannot be found in the service container
 *
 * @author clvarley
 */
Class NotFoundException Extends RuntimeException
{

    /**
     * @psalm-param class-string $name
     *
     * @param string $name Class/interface name
     */
    public function __construct( string $name )
    {
        parent::__construct(
            sprintf(
                'Container tried to resolve %s but found no matches. Are you sure it has been registered?',
                $name
            )
        );
    }
}
