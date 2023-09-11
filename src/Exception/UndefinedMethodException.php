<?php

namespace Swiftly\Dependency\Exception;

use Swiftly\Dependency\UndefinedStructureException;

use function sprintf;

/**
 * Exception used to indicate a method does not exist
 *
 * This exception is only thrown if the given class exists but the named method
 * is not defined on it or anywhere in it's inheritence chain. If thrown, this
 * probably indicates you are either incorrectly referring to a method that
 * actually exists on another class or you have spelled the method name wrong.
 *
 * @api
 */
final class UndefinedMethodException extends UndefinedStructureException
{
    /**
     * Indicate a method does not exist on a class
     *
     * @param class-string $class Fully qualified classname
     * @param string $method      Method name
     */
    public function __construct(string $class, string $method)
    {
        parent::__construct(
            sprintf(
                "Could not find a declaration for method '%s::%s()' are you sure it exists?",
                $class,
                $method
            )
        );
    }
}
