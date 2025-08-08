<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Closure;
use Swiftly\Dependency\Exception\ReflectionException;
use Swiftly\Dependency\Parameter;

/**
 * Defines methods used to inspect the types of function parameters.
 *
 * @api
 */
interface InspectorInterface
{
    /**
     * Inspect the parameters required to instantiate the given class.
     *
     * @param class-string $class
     *
     * @throws ReflectionException
     *
     * @return list<Parameter>
     */
    public function inspectClass(string $class): array;

    /**
     * Inspect the parameters of a named class method.
     *
     * @param class-string|object $class
     *
     * @throws ReflectionException
     *
     * @return list<Parameter>
     */
    public function inspectMethod(object|string $class, string $method): array;

    /**
     * Inspect the parameters of a named function or closure.
     *
     * @param Closure|callable-string $function
     *
     * @throws ReflectionException
     *
     * @return list<Parameter>
     */
    public function inspectFunction(Closure|string $function): array;
}
