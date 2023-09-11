<?php

namespace Swiftly\Dependency\Tests\Parameter;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Parameter\StringParameter;
use Swiftly\Dependency\Exception\UndefinedDefaultValueException;

/**
 * @covers \Swiftly\Dependency\Parameter
 * @covers \Swiftly\Dependency\Parameter\StringParameter
 */
final class StringParameterTest extends TestCase
{
    private StringParameter $parameter;

    public function setUp(): void
    {
        $this->parameter = new StringParameter(
            'value',
            false,
            static function () { return 'Hi!'; }
        );
    }

    public function testCanGetParameterName(): void
    {
        self::assertSame('value', $this->parameter->getName());
    }

    public function testCanGetParameterType(): void
    {
        self::assertSame('string', $this->parameter->getType());
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
        self::assertIsCallable($this->parameter->getDefaultCallback());
        self::assertSame('Hi!', ($this->parameter->getDefaultCallback())());
    }

    public function testCanCheckAcceptableInput(): void
    {
        self::assertTrue($this->parameter->accepts(''));
        self::assertTrue($this->parameter->accepts('Hi!'));

        self::assertFalse($this->parameter->accepts($this));
        self::assertFalse($this->parameter->accepts([]));
        self::assertFalse($this->parameter->accepts(null));
    }

    public function testCanAcceptAllScalarTypes(): void
    {
        self::assertTrue($this->parameter->accepts(42));
        self::assertTrue($this->parameter->accepts(3.14));
    }

    public function testCanAcceptNullWhenNullable(): void
    {
        $parameter = new StringParameter('param', true);

        self::assertTrue($parameter->isNullable());
        self::assertTrue($parameter->accepts(null));
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedDefaultValueException
     */
    public function testThrowsIfNoDefaultValueAvailable(): void
    {
        self::expectException(UndefinedDefaultValueException::class);

        (new StringParameter('value', false))->getDefaultCallback();
    }
}
