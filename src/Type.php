<?php

namespace Swiftly\Dependency;

use Closure;

use function is_object;
use function is_callable;
use function is_array;
use function method_exists;
use function is_string;
use function class_exists;

/**
 * Utility class containing static methods used for type inspection
 *
 * @psalm-type ArrayCallable = callable-array&array{0:class-string|object,1:string}
 * @internal
 */
abstract class Type
{
    /**
     * Determine if the subject is a service factory or service instance
     *
     * @template T
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
     * @psalm-assert-if-true ArrayCallable $subject
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
     * @param mixed $subject Subject variable
     * @return bool          Is invokable object?
     */
    final public static function isInvokable($subject): bool
    {
        return (is_object($subject) && method_exists($subject, '__invoke'));
    }

    /**
     * Determine if the subject is a valid class name
     *
     * @template T of object
     * @psalm-assert-if-true class-string<T> $subject
     * @param mixed $subject Subject variable
     * @return bool          Is class name?
     */
    final public static function isClassname($subject): bool
    {
        return (is_string($subject) && class_exists($subject));
    }
}
