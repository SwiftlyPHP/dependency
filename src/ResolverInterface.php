<?php

namespace Swiftly\Dependency;

use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Exception\UndefinedClassException;
use Swiftly\Dependency\Exception\UndefinedFunctionException;
use Swiftly\Dependency\Exception\UndefinedMethodException;
use Swiftly\Dependency\Exception\CompoundTypeException;

/**
 * Classes capable of resolving parameters of classes, methods and functions
 *
 * @api
 */
interface ResolverInterface
{
    /**
     * Resolve the dependencies required to instantiate a class
     *
     * @throws UndefinedClassException If the given class is undefined
     * @throws CompoundTypeException   If the constructor takes a compound type
     *
     * @param class-string $class Fully qualified classname
     * @return list<Parameter>    Parameter information
     */
    public function resolveClass(string $class): array;

    /**
     * Resolve the dependencies of a given class method
     *
     * @throws UndefinedClassException  If the given class is undefined
     * @throws UndefinedMethodException If the given method is undefined
     * @throws CompoundTypeException    If the method takes a compound type
     *
     * @param class-string|object $class Class FQN or instance
     * @param string $method             Method name
     * @return list<Parameter>           Parameter information
     */
    public function resolveMethod($class, string $method): array;

    /**
     * Resolve the dependencies of a given function
     *
     * @throws UndefinedFunctionException If the given function is undefined
     * @throws CompoundTypeException      If the function takes a compound type
     *
     * @param \Closure|callable-string $function Function name or closure
     * @return list<Parameter>                   Parameter information
     */
    public function resolveFunction($function): array;
}
