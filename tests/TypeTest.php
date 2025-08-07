<?php declare(strict_types=1);

namespace Swiftly\Dependency\Tests;

use Iterator;
use PHPUnit\Framework\TestCase;
use stdClass;
use Swiftly\Dependency\Type;

/**
 * @covers \Swiftly\Dependency\Type
 */
final class TypeTest extends TestCase
{
    public function testCanTellIfVariableIsObject(): void
    {
        self::assertTrue(Type::isServiceInstance($this));
        self::assertTrue(Type::isServiceInstance(new stdClass()));

        self::assertFalse(Type::isServiceInstance(static function () {}));
        self::assertFalse(Type::isServiceInstance([]));
        self::assertFalse(Type::isServiceInstance(null));
    }

    public function testCanTellIfVariableIsCallableMethod(): void
    {
        self::assertTrue(Type::isMethod([$this, 'testCanTellIfVariableIsObject']));
        self::assertTrue(Type::isMethod([Type::class, 'isMethod']));

        self::assertFalse(Type::isMethod([new stdClass(), '__invoke']));
        self::assertFalse(Type::isMethod(static function () {}));
        self::assertFalse(Type::isMethod('strlen'));
        self::assertFalse(Type::isMethod(null));
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
        self::assertSame('int', Type::getName(42));
        self::assertSame('float', Type::getName(3.14));
        self::assertSame('null', Type::getName(null));
        self::assertSame(stdClass::class, Type::getName(new stdClass()));
        self::assertSame(self::class, Type::getName($this));
    }
}
