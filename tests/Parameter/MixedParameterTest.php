<?php

namespace Swiftly\Dependency\Tests\Parameter;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Parameter\MixedParameter;
use Swiftly\Dependency\Exception\UndefinedDefaultValueException;
use stdClass;

/**
 * @covers \Swiftly\Dependency\Parameter
 * @covers \Swiftly\Dependency\Parameter\MixedParameter
 */
final class MixedParameterTest extends TestCase
{
    private MixedParameter $parameter;

    public function setUp(): void
    {
        $this->parameter = new MixedParameter(
            'value',
            function () { return $this; }
        );
    }

    public function testCanGetParameterName(): void
    {
        self::assertSame('value', $this->parameter->getName());
    }

    public function testCanGetParameterType(): void
    {
        self::assertSame('mixed', $this->parameter->getType());
    }

    public function testIsNotConsideredBuiltin(): void
    {
        self::assertFalse($this->parameter->isBuiltin());
    }

    public function testIsAlwaysNullable(): void
    {
        self::assertTrue($this->parameter->isNullable());
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

    public function testCanAcceptAllTypes(): void
    {
        self::assertTrue($this->parameter->accepts(true));
        self::assertTrue($this->parameter->accepts(false));
        self::assertTrue($this->parameter->accepts('Hi!'));
        self::assertTrue($this->parameter->accepts(42));
        self::assertTrue($this->parameter->accepts(3.14));
        self::assertTrue($this->parameter->accepts(null));
        self::assertTrue($this->parameter->accepts([]));
        self::assertTrue($this->parameter->accepts(new stdClass));
    }   

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedDefaultValueException
     */
    public function testThrowsIfNoDefaultValueAvailable(): void
    {
        self::expectException(UndefinedDefaultValueException::class);

        (new MixedParameter('value'))->getDefaultCallback();
    }
}
