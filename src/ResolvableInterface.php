<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Swiftly\Dependency\Exception\AttributeException;

/**
 * Contract for classes that can resolve function parameters.
 *
 * @template T
 */
interface ResolvableInterface
{
    /**
     * Attempt to resolve the value of a parameter.
     *
     * @param Parameter $definition The parameter that needs resolving.
     * @param Container $container  Dependency container instance.
     *
     * @throws AttributeException If the user has misconfigured the attribute.
     *
     * @return T|null The value to use or null if parameter cannot be resolved.
     */
    public function resolve(Parameter $definition, Container $container): mixed;
}
