<?php

namespace Swiftly\Dependency\Exception;

use ReflectionException;
use ReflectionParameter;

use function sprintf;

/**
 * Exception used to warn we do not yet support union/intersection types
 *
 * Allowing the use of compound types would dramatically increase the complexity
 * of the service container, so for the time being we only support functions and
 * methods where each parameter has a single type.
 *
 * @api
 */
final class CompoundTypeException extends ReflectionException
{
    /**
     * Warn that a function parameter is compound and cannot be reflected
     *
     * @param ReflectionParameter $parameter Function parameter information
     */
    public function __construct(ReflectionParameter $parameter)
    {
        parent::__construct(
            sprintf(
                "Could not resolve complex type of parameter \$%s to %s",
                $parameter->getName(),
                self::getFunctionName($parameter)
            )
        );
    }

    /**
     * Return the qualified name of the function this parameter applies to
     *
     * @param ReflectionParameter $parameter Function parameter information
     * @return string                        Function/method name
     */
    private static function getFunctionName(ReflectionParameter $parameter): string
    {
        $function = $parameter->getDeclaringFunction();
        $class = $parameter->getDeclaringClass();
        $name = $function->getName();

        if ($class) {
            $name = "{$class->getName()}::" . $name;
        }

        return $name;
    }
}
