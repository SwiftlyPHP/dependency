<?php declare(strict_types=1);

namespace Swiftly\Dependency;

/**
 * Contract for classes that can provide services to the container.
 *
 * @template T
 */
interface ProvideInterface
{
    /**
     * Provide the value required by the container.
     *
     * @return T|null
     */
    public function provide(Container $container): mixed;
}
