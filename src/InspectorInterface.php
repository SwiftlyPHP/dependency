<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Exception\UndefinedClassException;
use Swiftly\Dependency\Exception\UndefinedFunctionException;
use Swiftly\Dependency\Exception\UndefinedMethodException;
use Swiftly\Dependency\Exception\CompoundTypeException;
use Closure;

/**
 * Defines methods used to inspect the types of function parameters
 *
 * @api
 */
interface InspectorInterface
{
    /**
     * Inspect the parameters required to instantiate the given class
     *
     * @throws UndefinedClassException If the given class is undefined
     * @throws CompoundTypeException   If the constructor takes a compound type
     *
     * @param class-string $class Fully qualified classname
     * @return list<Parameter>    Parameter information
     */
    public function inspectClass(string $class): array;

    /**
     * Inspect the parameters of a named class method
     *
     * @throws UndefinedClassException  If the given class is undefined
     * @throws UndefinedMethodException If the given method is undefined
     * @throws CompoundTypeException    If the method takes a compound type
     *
     * @param class-string|object $class Class FQN or instance
     * @param string $method             Method name
     * @return list<Parameter>           Parameter information
     */
    public function inspectMethod($class, string $method): array;

    /**
     * Inspect the parameters of a named function or closure
     *
     * @throws UndefinedFunctionException If the given function is undefined
     * @throws CompoundTypeException      If the function takes a compound type
     *
     * @param Closure|callable-string $function Function name or closure
     * @return list<Parameter>                   Parameter information
     */
    public function inspectFunction($function): array;
}
