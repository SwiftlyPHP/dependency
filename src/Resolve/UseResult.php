<?php declare(strict_types=1);

namespace Swiftly\Dependency\Resolve;

use Attribute;
use Swiftly\Dependency\Container;
use Swiftly\Dependency\ContainerAwareInterface;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\ResolvableInterface;

/**
 * Will provide the parameter with the result of calling a method or function.
 *
 * @api
 *
 * @template T
 * @implements ResolvableInterface<T>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class UseResult implements ResolvableInterface, ContainerAwareInterface
{
    /**
     * @param callable(mixed):T $callback
     * @param array<non-empty-string, mixed> $parameters
     */
    public function __construct(
        private $callback,
        private array $parameters = [],
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Parameter $definition, Container $container): mixed
    {
        return $container->call($this->callback, $this->parameters);
    }
}
