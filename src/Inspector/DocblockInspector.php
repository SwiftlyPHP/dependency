<?php declare(strict_types=1);

namespace Swiftly\Dependency\Inspector;

use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Swiftly\Dependency\Exception\CompoundTypeException;
use Swiftly\Dependency\Exception\DocblockParseException;
use Swiftly\Dependency\Exception\UndefinedStructureException;
use Swiftly\Dependency\Exception\UnknownTypeException;
use Swiftly\Dependency\InspectorInterface;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Parameter\ArrayParameter;
use Swiftly\Dependency\Parameter\BooleanParameter;
use Swiftly\Dependency\Parameter\MixedParameter;
use Swiftly\Dependency\Parameter\NamedClassParameter;
use Swiftly\Dependency\Parameter\NumericParameter;
use Swiftly\Dependency\Parameter\ObjectParameter;
use Swiftly\Dependency\Parameter\StringParameter;
use Swiftly\Dependency\Type;

use function class_exists;
use function get_class;
use function is_object;
use function preg_match_all;
use function strpos;
use function substr;

use const PREG_SET_ORDER;

/**
 * Determines parameters by analysing developer authored docblocks.
 *
 * This class was created mostly as a proof-of-concept and an example of how the
 * `InspectorInterface` could be used for non-reflection based parameter
 * inspection. In almost all cases we recommend using the `ReflectionInspector`
 * class instead, as relying on (potentially incorrect) docblocks over language
 * level type-hints is almost certainly a recipe for disaster.
 *
 * @api
 */
class DocblockInspector implements InspectorInterface
{
    private const TYPE = '\\\?[A-Za-z\_\?][A-Za-z0-9\_\\\\|]*';
    private const IDENTIFIER = '[A-Za-z\_][A-Za-z0-9\_]*';
    private const DOCBLOCK = '/^[* ]+@(?:param|var)(?:\s+('.self::TYPE.'))?\s+\$('.self::IDENTIFIER.')/m';

    /** {@inheritDoc} */
    public function inspectClass(string $class): array
    {
        try {
            $reflected = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw UndefinedStructureException::createForClass($class);
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
                throw UndefinedStructureException::createForClass($class);
            } else {
                throw UndefinedStructureException::createForMethod(
                    $class,
                    $method
                );
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
            throw UndefinedStructureException::createForFunction($function);
        }

        return $this->inspect($reflected);
    }

    /**
     * Inspect a callable (of any type) to determine it's parameters.
     *
     *
     * @param ReflectionFunctionAbstract $abstract Reflected function
     * @throws CompoundTypeException If docblock contains a compound type
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
     * Parses parameter type information from the given docblock comment.
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

        /** @var list<array{1:string, 2:non-empty-string}> $matches */
        return $this->parseParameters($matches);
    }

    /**
     * Parse the parameter information returned by the regex match.
     *
     * @param list<array{1:string, 2:non-empty-string}> $parameters
     * @return list<Parameter> Stripped parameter information
     */
    private function parseParameters(array $parameters): array
    {
        $parsed = [];

        foreach ($parameters as $parameter) {
            $parsed[] = $this->parseParameter(
                $parameter[1] ?: Type::TYPE_MIXED,
                $parameter[2]
            );
        }

        return $parsed;
    }

    /**
     * Parse the given type and parameter name.
     *
     * @upgrade:php8.0 swap to using `match()` statement
     *
     * @param non-empty-string $type Type string
     * @param non-empty-string $name Parameter name
     * @throws DocblockParseException If the given type is compound
     * @return Parameter             Parameter information
     */
    private function parseParameter(string $type, string $name): Parameter
    {
        // Check if type is compound (has union or intersection)
        if (strpos($type, '&') !== false || strpos($type, '|') !== false) {
            throw new DocblockParseException($name);
        }

        $is_nullable = strpos($type, '?') === 0;

        /** @var non-empty-string $type */
        $type = $is_nullable ? substr($type, 1) : $type;

        switch ($type) {
            case Type::TYPE_ARRAY:
                return new ArrayParameter($name, $is_nullable);
            case Type::TYPE_BOOL:
                return new BooleanParameter($name, $is_nullable);
            case Type::TYPE_MIXED:
                return new MixedParameter($name);
            case Type::TYPE_INT:
            case Type::TYPE_FLOAT:
                return new NumericParameter($name, $type, $is_nullable);
            case Type::TYPE_STRING:
                return new StringParameter($name, $is_nullable);
            case Type::TYPE_OBJECT:
                return new ObjectParameter($name, $is_nullable);
            default:
                if (!Type::isClassname($type)) {
                    throw new UnknownTypeException($name, $type);
                }
                return new NamedClassParameter($name, $type, $is_nullable);
        }
    }
}
