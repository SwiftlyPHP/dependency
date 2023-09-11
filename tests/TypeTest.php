<?php

namespace Swiftly\Dependency\Tests;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Type;
use Iterator;
use stdClass;

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
        self::assertTrue(Type::isMethod([Type::class, 'isMethod']));

        self::assertFalse(Type::isMethod([new \stdClass(), '__invoke']));
        self::assertFalse(Type::isMethod(static function () {}));
        self::assertFalse(Type::isMethod('strlen'));
        self::assertFalse(Type::isMethod(null));
    }

    public function testCanTellIfVariableIsInvokableObject(): void
    {
        self::assertTrue(Type::isInvokableObject(new class {
            function __invoke(): void {}
        }));

        self::assertFalse(Type::isInvokableObject($this));
        self::assertFalse(Type::isInvokableObject(TestCase::class));
        self::assertFalse(Type::isInvokableObject(null));
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

    public function testCanGetVariableName(): void
    {
        self::assertSame('array', Type::getName([]));
        self::assertSame('string', Type::getName('Hi!'));
        self::assertSame('integer', Type::getName(42));
        self::assertSame('double', Type::getName(3.14));
        self::assertSame('NULL', Type::getName(null));
        self::assertSame(stdClass::class, Type::getName(new stdClass));
        self::assertSame(self::class, Type::getName($this));
    }
}
