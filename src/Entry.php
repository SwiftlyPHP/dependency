<?php

namespace Swiftly\Dependency;

use function in_array;

/**
 * Stores information regarding a single service/entry in the service container
 *
 * @internal
 * @readonly
 * @template T
 */
final class Entry
{
    /** Unique service ID */
    public string $id;

    /** @var class-string $type */
    public string $type;

    /**
     * @psalm-var (callable():T)|null $factory
     * @var callable|null $factory
     */
    public $factory;

    /** @var list<string> $tags */
    public array $tags;

    /**
     * Create a new entry in the register
     *
     * @psalm-param (callable():T)|null $factory
     * @param string $id             Service ID
     * @param class-string<T> $type  Fully qualified classname
     * @param callable|null $factory Service factory
     * @param list<string> $tags     Service tags
     */
    public function __construct(
        string $id,
        string $type,
        ?callable $factory = null,
        array $tags = []
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->factory = $factory;
        $this->tags = $tags;
    }

    /**
     * Determine if this entry has a given tag
     *
     * @param string $tag Tag name
     * @return bool       Entry has tag
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }
}
