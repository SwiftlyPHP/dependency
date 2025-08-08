<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * @internal
 * @static
 */
final class Debug
{
    /**
     * Returns the fully-qualified function name.
     *
     * @return non-empty-string
     */
    public static function getFunctionName(
        ReflectionFunctionAbstract $function,
    ): string {
        $name = $function->getName();

        if ($function instanceof ReflectionMethod) {
            $name = $function->getDeclaringClass()->getName() . '::' . $name;
        }

        return $name;
    }
}
