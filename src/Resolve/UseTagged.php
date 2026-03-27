<?php declare(strict_types=1);

namespace Swiftly\Dependency\Resolve;

use Attribute;
use Swiftly\Dependency\Container;
use Swiftly\Dependency\ContainerAwareInterface;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\ResolvableInterface;

/**
 * Will provide a parameter all services that have a given tag.
 *
 * @api
 *
 * @template T of object
 * @implements ResolvableInterface<list<T>>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class UseTagged implements ResolvableInterface, ContainerAwareInterface
{
    /**
     * @param non-empty-string $tag
     * @param class-string<T>|null $type
     */
    public function __construct(
        private string $tag,
        private string|null $type = null,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Parameter $definition, Container $container): array
    {
        return $container->tagged($this->tag, $this->type);
    }
}
