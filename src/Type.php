<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Closure;

use function class_exists;
use function get_class;
use function gettype;
use function interface_exists;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;

/**
 * Utility class containing static methods used for type inspection.
 *
 * The `TYPE_*` constants here should match the names returned for the built-in
 * types by the {@see \ReflectionNamedType::getName} method.
 *
 * @internal
 *
 * @psalm-type callable-method = list{class-string|object, non-empty-string}
 */
abstract class Type
{
    public const TYPE_ARRAY = 'array';
    public const TYPE_BOOL = 'bool';
    public const TYPE_MIXED = 'mixed';
    public const TYPE_INT = 'int';
    public const TYPE_FLOAT = 'float';
    public const TYPE_STRING = 'string';
    public const TYPE_OBJECT = 'object';

    /**
     * Determine if the subject is a service factory or service instance.
     *
     * @template T of object
     *
     * @psalm-assert-if-true T $subject
     *
     * @param T|ProvideInterface<T>|callable():T $subject
     */
    final public static function isServiceInstance($subject): bool
    {
        return is_object($subject)
            && !(
                $subject instanceof Closure
                || $subject instanceof ProvideInterface
            );
    }

    /**
     * Determine if the subject is a class method callable.
     *
     * @psalm-assert-if-true callable-array&callable-method $subject
     * @param mixed $subject Callable variable
     * @return bool          Is method call?
     */
    final public static function isMethod($subject): bool
    {
        return is_callable($subject) && is_array($subject);
    }

    /**
     * Determine if the subject is a valid class or interface name.
     *
     * @psalm-assert-if-true class-string $subject
     * @param mixed $subject Subject variable
     * @return bool          Is class name?
     */
    final public static function isClassname($subject): bool
    {
        return is_string($subject)
            && (class_exists($subject) || interface_exists($subject));
    }

    /**
     * Return a user-friendly type descriptor.
     *
     * @param mixed $subject Subject variable
     * @return string        Type name
     * @psalm-return ($subject is object ? class-string : string)
     */
    final public static function getName(mixed $subject): string
    {
        return get_debug_type($subject);
    }
}
