<?php

namespace Swiftly\Dependency\Exception;

use ReflectionException;

use function sprintf;

/**
 * Exception used to indicate a named class cannot be found or does not exist
 *
 * @api
 */
final class UndefinedClassException extends ReflectionException
{
    /**
     * Indicate the named class does not exist
     *
     * @param string $class Fully qualified classname
     */
    public function __construct(string $class)
    {
        parent::__construct(
            sprintf(
                "Could not find a declaration for class '%s' are you sure it exists?",
                $class
            )
        );
    }
}
