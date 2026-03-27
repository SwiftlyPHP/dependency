<?php declare(strict_types=1);

namespace Swiftly\Dependency\Resolve;

use Attribute;
use Swiftly\Dependency\Container;
use Swiftly\Dependency\ContainerAwareInterface;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\ResolvableInterface;

/**
 * Will provide a parameter with the named concrete implementation.
 *
 * Useful when you want to use an interface type hint for a parameter, but you
 * want to force the use of a known class.
 *
 * @api
 *
 * @template T of object
 * @implements ResolvableInterface<T>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class UseConcrete implements ResolvableInterface, ContainerAwareInterface
{
    /**
     * @param class-string<T> $class
     */
    public function __construct(
        private string $class,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(
        Parameter $definition,
        Container $container,
    ): object|null {
        return $container->get($this->class);
    }
}
