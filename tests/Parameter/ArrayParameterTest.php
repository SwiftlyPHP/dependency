<?php

namespace Swiftly\Dependency\Tests\Parameter;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Parameter\ArrayParameter;
use Swiftly\Dependency\Exception\UndefinedDefaultValueException;

final class ArrayParameterTest extends TestCase
{
    private ArrayParameter $parameter;

    public function setUp(): void
    {
        $this->parameter = new ArrayParameter(
            'value',
            false,
            static function () { return ['Hi!']; }
        );
    }

    public function testCanGetParameterName(): void
    {
        self::assertSame('value', $this->parameter->getName());
    }

    public function testCanGetParameterType(): void
    {
        self::assertSame('array', $this->parameter->getType());
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
        self::assertSame(['Hi!'], $this->parameter->getDefault());
    }

    public function testCanCheckAcceptableInput(): void
    {
        self::assertTrue($this->parameter->accepts([]));

        self::assertFalse($this->parameter->accepts($this));
        self::assertFalse($this->parameter->accepts('Hi!'));
        self::assertFalse($this->parameter->accepts(null));
    }

    public function testThrowsIfNoDefaultValueAvailable(): void
    {
        self::expectException(UndefinedDefaultValueException::class);

        (new ArrayParameter('value', false))->getDefault();
    }
}