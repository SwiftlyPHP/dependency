<?php

namespace Swiftly\Dependency\Inspector;

use Swiftly\Dependency\InspectorInterface;
use Swiftly\Dependency\Exception\UndefinedClassException;
use Swiftly\Dependency\Exception\UndefinedMethodException;
use Swiftly\Dependency\Exception\UndefinedFunctionException;
use Swiftly\Dependency\Exception\DocblockParseException;
use Swiftly\Dependency\Exception\CompoundTypeException;
use Swiftly\Dependency\Parameter;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionFunctionAbstract;

use function is_object;
use function get_class;
use function class_exists;
use function preg_match_all;
use function strpos;
use function substr;
use function in_array;

use const PREG_SET_ORDER;

/**
 * Determines parameters by analysing developer authored docblocks
 *
 * This class was created mostly as a proof-of-concept and an example of how the
 * `InspectorInterface` could be used for non-reflection based parameter
 * inspection. In almost all cases we recommend using either the
 * `ReflectionInspector` or `CachedInspector` classes instead, as relying on
 * (potentially incorrect) docblocks instead of utilizing language level
 * type-hints is a recipe for disaster.
 *
 * @api
 */
class DocblockInspector implements InspectorInterface
{
    private const TYPE = '[A-Za-z\_\?][A-Za-z0-9\_\|]+';
    private const IDENTIFIER = '[A-Za-z\_][A-Za-z0-9\_]+';
    private const DOCBLOCK = '/^[* ]+@(?:param|var)\s+('.self::TYPE.')\s+\$('.self::IDENTIFIER.')/m';
    private const BUILTIN = ['int', 'integer', 'float', 'double', 'bool', 'boolean', 'string'];

    /** {@inheritDoc} */
    public function inspectClass(string $class): array
    {
        try {
            $reflected = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new UndefinedClassException($class);
        }

        $constructor = $reflected->getConstructor();

        // No constructor
        if ($constructor === null) {
            return [];
        }

        return $this->inspect($constructor);
    }

    /** {@inheritDoc} */
    public function inspectMethod($class, string $method): array
    {
        try {
            $reflected = new ReflectionMethod($class, $method);
        } catch (ReflectionException $e) {
            $class = is_object($class) ? get_class($class) : $class;

            if (!class_exists($class)) {
                throw new UndefinedClassException($class);
            } else {
                throw new UndefinedMethodException($class, $method);
            }
        }

        return $this->inspect($reflected);
    }

    /** {@inheritDoc} */
    public function inspectFunction($function): array
    {
        try {
            $reflected = new ReflectionFunction($function);
        } catch (ReflectionException $e) {
            /** @var string $function */
            throw new UndefinedFunctionException($function);
        }

        return $this->inspect($reflected);
    }

    /**
     * Inspect a callable (of any type) to determine it's parameters
     *
     * @throws CompoundTypeException If docblock contains a compound type
     *
     * @param ReflectionFunctionAbstract $abstract Reflected function
     * @return list<Parameter>                     Parsed parameters
     */
    private function inspect(ReflectionFunctionAbstract $abstract): array
    {
        if (($docblock = $abstract->getDocComment()) === false) {
            return [];
        }

        try {
            $parameters = $this->parseDocblock($docblock);
        } catch (DocblockParseException $e) {
            throw new CompoundTypeException($e->getParameterName(), $abstract);
        }

        return $parameters;
    }

    /**
     * Parses parameter type information from the given docblock comment
     *
     * @param string $docblock Docblock comment
     * @return list<Parameter> Parsed parameters
     */
    private function parseDocblock(string $docblock): array
    {
        if (empty($docblock)
            || !preg_match_all(self::DOCBLOCK, $docblock, $matches, PREG_SET_ORDER)
        ) {
            return [];
        }

        /** @var list<array{0:string,1:string,2:string}> $matches */
        return $this->parseParameters($matches);
    }

    /**
     * Parse the parameter information returned by the regex match
     *
     * @param list<array{0:string,1:string,2:string}> $parameters
     * @return list<Parameter> Stripped parameter information
     */
    private function parseParameters(array $parameters): array
    {
        $parsed = [];

        foreach ($parameters as $parameter) {
            $parsed[] = $this->parseParameter($parameter[1], $parameter[2]);
        }

        return $parsed;
    }

    /**
     * Parse the given type and parameter name
     *
     * @throws DocblockParseException If the given type is compound
     *
     * @param string $type Type string
     * @param string $name Parameter name
     * @return Parameter   Parameter information
     */
    private function parseParameter(string $type, string $name): Parameter
    {
        // Check if type is compound (has union or intersection)
        if (strpos($type, '&') !== false || strpos($type, '|') !== false) {
            throw new DocblockParseException($name);
        }

        // Strip nullable (?) modifier if present
        $type = (strpos($type, '?') === 0 ? substr($type, 1) : $type);

        return new Parameter(
            $name,
            $type,
            null, // Can't determine default from docblock
            in_array($type, self::BUILTIN)
        );
    }
}
