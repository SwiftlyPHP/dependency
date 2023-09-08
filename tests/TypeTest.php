<?php

namespace Swiftly\Dependency\Tests;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Type;
use Iterator;

/**
 * @covers \Swiftly\Dependency\Type
 */
final class TypeTest extends TestCase
{
    public function testCanTellIfVariableIsObject(): void
    {
        self::assertTrue(Type::isServiceInstance($this));
        self::assertTrue(Type::isServiceInstance(new \stdClass()));
        
        self::assertFalse(Type::isServiceInstance(static function () {}));
        self::assertFalse(Type::isServiceInstance([]));
        self::assertFalse(Type::isServiceInstance(null));
    }

    public function testCanTellIfVariableIsCallableMethod(): void
    {
        self::assertTrue(Type::isMethod([$this, 'testCanTellIfVariableIsObject']));
        self::assertTrue(Type::isMethod([TestCase::class, 'count']));
        self::assertTrue(Type::isMethod([Type::class, 'isMethod']));

        self::assertFalse(Type::isMethod([new \stdClass(), '__invoke']));
        self::assertFalse(Type::isMethod(static function () {}));
        self::assertFalse(Type::isMethod('strlen'));
        self::assertFalse(Type::isMethod(null));
    }

    public function testCanTellIfVariableIsInvokable(): void
    {
        self::assertTrue(Type::isInvokable(new class { public function __invoke() {} }));
        self::assertTrue(Type::isInvokable(static function () {}));

        self::assertFalse(Type::isInvokable($this));
        self::assertFalse(Type::isInvokable(TestCase::class));
        self::assertFalse(Type::isInvokable(null));
    }

    public function testCanTellIfVariableIsClassName(): void
    {
        self::assertTrue(Type::isClassname(TestCase::class));
        self::assertTrue(Type::isClassname('stdClass'));

        self::assertFalse(Type::isClassname('UnknownClass'));
        self::assertFalse(Type::isClassname('strlen'));
        self::assertFalse(Type::isClassname(null));
    }

    public function testCanTellIfVariableIsInterfaceName(): void
    {
        self::assertTrue(Type::isClassname(Iterator::class));
    }
}
