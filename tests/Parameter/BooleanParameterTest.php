<?php

namespace Swiftly\Dependency\Tests\Parameter;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Parameter\BooleanParameter;
use Swiftly\Dependency\Exception\UndefinedDefaultValueException;

/**
 * @covers \Swiftly\Dependency\Parameter
 * @covers \Swiftly\Dependency\Parameter\BooleanParameter
 */
final class BooleanParameterTest extends TestCase
{
    private BooleanParameter $parameter;

    public function setUp(): void
    {
        $this->parameter = new BooleanParameter(
            'value',
            false,
            static function () { return true; }
        );
    }

    public function testCanGetParameterName(): void
    {
        self::assertSame('value', $this->parameter->getName());
    }

    public function testCanGetParameterType(): void
    {
        self::assertSame('bool', $this->parameter->getType());
    }

    public function testIsConsideredBuiltin(): void
    {
        self::assertTrue($this->parameter->isBuiltin());
    }

    public function testCanDetermineIfNullable(): void
    {
        self::assertFalse($this->parameter->isNullable());
    }

    public function testCanDetermineIfDefaultValueAvailable(): void
    {
        self::assertTrue($this->parameter->hasDefault());
    }

    public function testCanGetDefaultValue(): void
    {
        self::assertSame(true, $this->parameter->getDefault());
    }

    public function testCanCheckAcceptableInput(): void
    {
        self::assertTrue($this->parameter->accepts(true));
        self::assertTrue($this->parameter->accepts(false));

        self::assertFalse($this->parameter->accepts($this));
        self::assertFalse($this->parameter->accepts([]));
        self::assertFalse($this->parameter->accepts(null));
    }

    public function testCanAcceptAllScalarTypes(): void
    {
        self::assertTrue($this->parameter->accepts(''));
        self::assertTrue($this->parameter->accepts('Hi!'));
        self::assertTrue($this->parameter->accepts(42));
        self::assertTrue($this->parameter->accepts(3.14));
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedDefaultValueException
     */
    public function testThrowsIfNoDefaultValueAvailable(): void
    {
        self::expectException(UndefinedDefaultValueException::class);

        (new BooleanParameter('value', false))->getDefault();
    }
}
