<?php

namespace Swiftly\Dependency\Exception;

use ReflectionException;
use ReflectionParameter;

use function sprintf;

/**
 * Exception used to warn we do not yet support union/intersection types
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
                "Failed resolving union/intersection type for parameter \$%s of %s",
                $parameter->getName(),
                $this->getFunctionName($parameter)
            )
        );
    }

    /**
     * Return the qualified name of the function this parameter applies to
     *
     * @param ReflectionParameter $parameter Function parameter information
     * @return string                        Function/method name
     */
    private function getFunctionName(ReflectionParameter $parameter): string
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
