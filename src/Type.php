<?php

namespace Swiftly\Dependency;

use Closure;

use function is_object;
use function is_callable;
use function is_array;
use function method_exists;
use function is_string;
use function class_exists;
use function interface_exists;
use function get_class;
use function gettype;

/**
 * Utility class containing static methods used for type inspection
 *
 * @psalm-type callable-method = list{class-string|object,non-empty-string}
 * @internal
 */
abstract class Type
{
    /**
     * Determine if the subject is a service factory or service instance
     *
     * @template T of object
     * @psalm-assert-if-true T $subject
     * @psalm-param T|callable():T $subject
     * @param object|callable $subject Service factory or instance
     * @return bool                    Is object instance?
     */
    final public static function isServiceInstance($subject): bool
    {
        return (is_object($subject) && !($subject instanceof Closure));
    }

    /**
     * Determine if the subject is a class method callable
     *
     * @psalm-assert-if-true callable-array&callable-method $subject
     * @param mixed $subject Callable variable
     * @return bool          Is method call?
     */
    final public static function isMethod($subject): bool
    {
        return (is_callable($subject) && is_array($subject));
    }

    /**
     * Determine if the subject is an invokable object
     *
     * @psalm-assert-if-true callable-object $subject
     * @psalm-assert-if-true !Closure $subject
     * @param mixed $subject Subject variable
     * @return bool          Is invokable object?
     */
    final public static function isInvokableObject($subject): bool
    {
        return (is_object($subject)
            && !($subject instanceof Closure)
            && method_exists($subject, '__invoke')
        );
    }

    /**
     * Determine if the subject is a valid class or interface name
     *
     * @psalm-assert-if-true class-string $subject
     * @param mixed $subject Subject variable
     * @return bool          Is class name?
     */
    final public static function isClassname($subject): bool
    {
        return (is_string($subject)
            && (class_exists($subject) || interface_exists($subject))
        );
    }

    /**
     * Return a user-friendly type descriptor
     *
     * @php:8.0 Swap to using `get_debug_type`
     * @psalm-return ($subject is object ? class-string : string)
     * @param mixed $subject Subject variable
     * @return string        Type name
     */
    final public static function getName($subject): string
    {
        return (is_object($subject) ? get_class($subject) : gettype($subject));
    }
}
