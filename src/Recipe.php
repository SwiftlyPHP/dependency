<?php declare(strict_types=1);

namespace Swiftly\Dependency;

use Closure;

use function in_array;

/**
 * Stores information required for the container to create a service.
 *
 * @template T of object
 */
final class Recipe
{
    /** @var list<non-empty-string> */
    public array $tags = [];

    /** @var array<non-empty-string,mixed> */
    public array $arguments = [];

    public bool $isSingleton = true;

    /**
     * @param class-string<T> $className
     * @param null|Closure():T $factory
     */
    public function __construct(
        public readonly string $className,
        public readonly null|Closure $factory,
    ) {
    }

    /**
     * @param list<non-empty-string> $tags
     *
     * @return $this
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param array<non-empty-string,mixed> $arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return $this
     */
    public function setSingleton(bool $singleton): self
    {
        $this->isSingleton = $singleton;

        return $this;
    }

    /**
     * @param non-empty-string $tag
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }
}
