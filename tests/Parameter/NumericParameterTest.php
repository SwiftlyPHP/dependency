<?php

namespace Swiftly\Dependency\Tests\Parameter;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Parameter\NumericParameter;
use Swiftly\Dependency\Exception\UndefinedDefaultValueException;

/**
 * @covers \Swiftly\Dependency\Parameter
 * @covers \Swiftly\Dependency\Parameter\NumericParameter
 */
final class NumericParameterTest extends TestCase
{
    private NumericParameter $parameter;

    public function setUp(): void
    {
        $this->parameter = new NumericParameter(
            'value',
            'float',
            false,
            static function () { return 3.14; }
        );
    }

    public function testCanGetParameterName(): void
    {
        self::assertSame('value', $this->parameter->getName());
    }

    public function testCanGetParameterType(): void
    {
        self::assertSame('float', $this->parameter->getType());
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
        self::assertSame(3.14, ($this->parameter->getDefaultCallback())());
    }

    public function testCanCheckAcceptableInput(): void
    {
        self::assertTrue($this->parameter->accepts(42));
        self::assertTrue($this->parameter->accepts(3.14));

        self::assertFalse($this->parameter->accepts($this));
        self::assertFalse($this->parameter->accepts([]));
        self::assertFalse($this->parameter->accepts(null));
    }

    public function testCanAcceptNumericStrings(): void
    {
        self::assertTrue($this->parameter->accepts('42'));
        self::assertTrue($this->parameter->accepts('3.14'));
        self::assertTrue($this->parameter->accepts('-10'));
        self::assertTrue($this->parameter->accepts('0.5'));

        self::assertFalse($this->parameter->accepts('numeric_string'));
        self::assertFalse($this->parameter->accepts('Hi!'));
        self::assertFalse($this->parameter->accepts(''));
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedDefaultValueException
     */
    public function testThrowsIfNoDefaultValueAvailable(): void
    {
        self::expectException(UndefinedDefaultValueException::class);

        (new NumericParameter('value', 'float', false))->getDefaultCallback();
    }
}
