<?php

namespace Swiftly\Dependency;

use function in_array;

/**
 * Stores information regarding a single service entry in the service container
 *
 * @template T of object
 */
final class Entry
{
    /** @var class-string<T> $type */
    public string $type;

    /**
     * @var null|callable():T $factory
     */
    public $factory;

    /** @var list<non-empty-string> $tags */
    public array $tags;

    /** @var array<non-empty-string,mixed> $args Manually passed arguments */
    public array $arguments;

    /**
     * Create a new entry in the register
     *
     *
     * @internal
     * @psalm-param null|callable():T $factory
     * @param class-string<T> $type        Fully qualified classname
     * @param callable|null $factory       Service factory
     * @param list<non-empty-string> $tags Service tags
     */
    public function __construct(
        string $type,
        ?callable $factory = null,
        array $tags = []
    ) {
        $this->type = $type;
        $this->factory = $factory;
        $this->tags = $tags;
        $this->arguments = [];
    }

    /**
     * Determine if this entry has a given tag
     *
     * @internal
     * @param string $tag Tag name
     * @return bool       Entry has tag
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }

    /**
     * Set the tags that apply to this service entry
     *
     * @api
     * @param list<non-empty-string> $tags Service tags
     * @return self                        Chainable interface
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Pass initialization arguments manually
     *
     * @api
     * @param array<non-empty-string,mixed> $arguments Manual factory arguments
     * @return self                                    Chainable interface
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Create a new service entry from a pre-existing object instance
     *
     * @internal
     * @template K of object
     * @param class-string<K> $type        Fully qualified classname
     * @param K $instance                  Object instance
     * @param list<non-empty-string> $tags Service tags
     * @return self<K>                     Service entry
     */
    public static function fromInstance(
        string $type,
        object $instance,
        array $tags = []
    ): self {
        return new self(
            $type,
            static function () use ($instance): object {
                return $instance;
            },
            $tags
        );
    }
}
