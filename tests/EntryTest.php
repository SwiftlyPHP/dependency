<?php

namespace Swiftly\Dependency\Tests;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Entry;

/**
 * @covers \Swiftly\Dependency\Entry
 */
final class EntryTest extends TestCase
{
    private Entry $entry;

    public function setUp(): void
    {
        $this->entry = new Entry(
            TestCase::class,
            function () {
                return $this;
            },
            ['test', 'example']
        );
    }

    public function testCanCreateEntryFromInstance(): void
    {
        $entry = Entry::fromInstance(
            TestCase::class,
            $this,
            ['test', 'example']
        );

        self::assertSame(TestCase::class, $entry->type);
        self::assertSame($this, ($entry->factory)());
        self::assertSame(['test', 'example'], $entry->tags);
    }

    public function testCanCheckIfEntryHasTag(): void
    {
        self::assertTrue($this->entry->hasTag('test'));
        self::assertTrue($this->entry->hasTag('example'));
        self::assertFalse($this->entry->hasTag('sample'));
    }

    public function testCanSetTags(): void
    {
        $return = $this->entry->setTags(['new', 'tags']);

        // The chainable return value is part of the API
        self::assertSame($this->entry, $return);
        self::assertCount(2, $this->entry->tags);
        self::assertContains('new', $this->entry->tags);
        self::assertContains('tags', $this->entry->tags);
    }

    public function testCanSetArguments(): void
    {
        $return = $this->entry->setArguments([
            'name' => 'John',
            'age' => 42
        ]);

        // The chainable return value is part of the API
        self::assertSame($this->entry, $return);
        self::assertCount(2, $this->entry->arguments);
        self::assertArrayHasKey('name', $this->entry->arguments);
        self::assertSame('John', $this->entry->arguments['name']);
        self::assertArrayHasKey('age', $this->entry->arguments);
        self::assertSame(42, $this->entry->arguments['age']);
    }

    public function testCanSetCacheableFlag(): void
    {
        $return = $this->entry->setOnce(false);

        // The chainable return value is part of the API
        self::assertSame($this->entry, $return);
        self::assertFalse($this->entry->once);
    }

    public function testIsCacheableByDefault(): void
    {
        self::assertTrue($this->entry->once);
    }
}
