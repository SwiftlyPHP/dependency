<?php

namespace Swiftly\Dependency\Tests\Parameter;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Parameter\NamedClassParameter;
use Swiftly\Dependency\Exception\UndefinedDefaultValueException;
use stdClass;

/**
 * @covers \Swiftly\Dependency\Parameter
 * @covers \Swiftly\Dependency\Parameter\NamedClassParameter
 */
final class NamedClassParameterTest extends TestCase
{
    private NamedClassParameter $parameter;

    public function setUp(): void
    {
        $this->parameter = new NamedClassParameter(
            'value',
            TestCase::class,
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
        self::assertSame(TestCase::class, $this->parameter->getType());
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
        self::assertSame($this, $this->parameter->getDefault());
    }

    public function testCanCheckAcceptableInput(): void
    {
        self::assertTrue($this->parameter->accepts($this));
        self::assertTrue($this->parameter->accepts(new class extends TestCase {}));

        self::assertFalse($this->parameter->accepts('Hi!'));
        self::assertFalse($this->parameter->accepts(42));
        self::assertFalse($this->parameter->accepts(3.14));
        self::assertFalse($this->parameter->accepts([]));
        self::assertFalse($this->parameter->accepts(new stdClass));
        self::assertFalse($this->parameter->accepts(null));
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedDefaultValueException
     */
    public function testThrowsIfNoDefaultValueAvailable(): void
    {
        self::expectException(UndefinedDefaultValueException::class);

        (new NamedClassParameter('value', stdClass::class, false))->getDefault();
    }
}
