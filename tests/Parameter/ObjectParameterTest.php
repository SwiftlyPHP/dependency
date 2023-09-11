<?php

namespace Swiftly\Dependency\Tests\Parameter;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Parameter\ObjectParameter;
use Swiftly\Dependency\Exception\UndefinedDefaultValueException;
use stdClass;

/**
 * @covers \Swiftly\Dependency\Parameter
 * @covers \Swiftly\Dependency\Parameter\ObjectParameter
 */
final class ObjectParameterTest extends TestCase
{
    private ObjectParameter $parameter;

    public function setUp(): void
    {
        $this->parameter = new ObjectParameter(
            'value',
            false,
            function () { return $this; }
        );
    }

    public function testCanGetParameterName(): void
    {
        self::assertSame('value', $this->parameter->getName());
    }

    public function testCanGetParameterType(): void
    {
        self::assertSame('object', $this->parameter->getType());
    }

    public function testIsNotConsideredBuiltin(): void
    {
        self::assertFalse($this->parameter->isBuiltin());
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
        self::assertSame($this, ($this->parameter->getDefaultCallback())());
    }

    public function testCanCheckAcceptableInput(): void
    {
        self::assertTrue($this->parameter->accepts($this));
        self::assertTrue($this->parameter->accepts((object)[]));
        self::assertTrue($this->parameter->accepts(new stdClass));
        self::assertTrue($this->parameter->accepts(new class {}));

        self::assertFalse($this->parameter->accepts('Hi!'));
        self::assertFalse($this->parameter->accepts(42));
        self::assertFalse($this->parameter->accepts(3.14));
        self::assertFalse($this->parameter->accepts([]));
        self::assertFalse($this->parameter->accepts(null));
    }

    public function testCanAcceptNullWhenNullable(): void
    {
        $parameter = new ObjectParameter('param', true);

        self::assertTrue($parameter->isNullable());
        self::assertTrue($parameter->accepts(null));
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedDefaultValueException
     */
    public function testThrowsIfNoDefaultValueAvailable(): void
    {
        self::expectException(UndefinedDefaultValueException::class);

        (new ObjectParameter('value', false))->getDefaultCallback();
    }
}
