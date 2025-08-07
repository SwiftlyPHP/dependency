<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use RuntimeException;

use function sprintf;

/**
 * Interface for all exceptions that occur when a code structure is undefined.
 *
 * Is thrown when the container encounters the following:
 * * When trying to resolve a class that doesn't exist
 * * When trying to call a method that doesn't exist
 * * When trying to call a function that doesn't exist
 *
 * This exception likely indicates a change in logic is needed.
 *
 * @api
 */
final class UndefinedStructureException extends RuntimeException
{
    /**
     * Indicate the given class does not exist.
     *
     * @param string $class Fully qualified class name
     * @return self
     */
    public static function createForClass(string $class): self
    {
        return new self(sprintf(
            "Could not find a declaration for class '%s' are you sure it exists?",
            $class
        ));
    }

    /**
     * Indicate that the named method does not exist on the given class.
     *
     * @param class-string $class Fully qualified class name
     * @param string $method      Method name
     * @return self
     */
    public static function createForMethod(string $class, string $method): self
    {
        return new self(sprintf(
            "Could not find a declaration for method '%s::%s()' are you sure it exists?",
            $class,
            $method
        ));
    }

    /**
     * Indicate the given function does not exist.
     *
     * @param string $function Function name
     * @return self
     */
    public static function createForFunction(string $function): self
    {
        return new self(sprintf(
            "Could not find a declaration for function '%s()' are you sure it exists?",
            $function
        ));
    }
}
