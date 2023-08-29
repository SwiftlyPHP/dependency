<?php

namespace Swiftly\Dependency\Exception;

use ReflectionException;

use function sprintf;

/**
 * Exception used to indicate a function does not exist
 *
 * @api
 */
final class UndefinedFunctionException extends ReflectionException
{
    /**
     * Indicate given named function does not exists
     *
     * @param string $function Function name
     */
    public function __construct(string $function)
    {
        parent::__construct(
            sprintf(
                "Could not find a declaration for function '%s()' are you sure it exists?",
                $function
            )
        );
    }
}
