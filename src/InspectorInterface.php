<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Closure;
use Swiftly\Dependency\Exception\CompoundTypeException;
use Swiftly\Dependency\Exception\UndefinedStructureException;
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
     *
     * @param class-string $class Fully qualified classname.
     * @throws UndefinedStructureException
     *      If the class is undefined.
     * @throws CompoundTypeException
     *      If the constructor takes a compound type.
     * @return list<Parameter>    Parameter information.
     */
    public function inspectClass(string $class): array;

    /**
     * Inspect the parameters of a named class method.
     *
     *
     * @param class-string|object $class Class FQN or instance.
     * @param string $method             Method name.
     * @throws UndefinedStructureException If the class or method is undefined.
     * @throws CompoundTypeException       If the method takes a compound type.
     * @return list<Parameter>           Parameter information.
     */
    public function inspectMethod(object|string $class, string $method): array;

    /**
     * Inspect the parameters of a named function or closure.
     *
     *
     * @param Closure|callable-string $function Function name or closure.
     * @throws UndefinedStructureException If the given function is undefined.
     * @throws CompoundTypeException       If the function takes a compound type.
     * @return list<Parameter>                   Parameter information.
     */
    public function inspectFunction(Closure|string $function): array;
}
