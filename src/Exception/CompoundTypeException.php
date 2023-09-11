<?php

namespace Swiftly\Dependency\Exception;

use Swiftly\Dependency\ParameterException;
use ReflectionFunctionAbstract;
use ReflectionMethod;

use function sprintf;
use function is_string;

/**
 * Exception used to warn we do not yet support union/intersection types
 *
 * Allowing the use of compound types would dramatically increase the complexity
 * of the service container, so for the time being we only support functions and
 * methods where each parameter has a single type.
 *
 * @api
 */
final class CompoundTypeException extends ParameterException
{
    /**
     * Warn that a function parameter is compound and cannot be reflected
     *
     * @param non-empty-string $parameter                 Parameter name
     * @param string|ReflectionFunctionAbstract $function Function or method
     */
    public function __construct(string $parameter, $function)
    {
        $this->parameter = $parameter;

        parent::__construct(
            sprintf(
                "Could not resolve complex type of parameter '\$%s' to %s()",
                $parameter,
                is_string($function) ? $function : self::getName($function)
            )
        );
    }

    /**
     * Return the fully qualified name of the reflected function
     *
     * @param ReflectionFunctionAbstract $abstract Reflected function
     * @return non-empty-string                    Function name
     */
    protected static function getName(
        ReflectionFunctionAbstract $abstract
    ): string {
        $name = $abstract->getName();

        if ($abstract instanceof ReflectionMethod) {
            $name = $abstract->getDeclaringClass()->getName() . '::' . $name;
        }

        return $name;
    }
}
