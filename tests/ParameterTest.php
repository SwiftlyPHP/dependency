<?php

namespace Swiftly\Dependency\Tests;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\InspectorInterface;
use stdClass;
use Swiftly\Dependency\Inspector\ReflectionInspector;

/**
 * @covers \Swiftly\Dependency\Parameter
 * @uses \Swiftly\Dependency\InspectorInterface
 */
final class ParameterTest extends TestCase
{
    public function exampleParameterProvider(): array
    {
        return [
            'int parameter' => [
                new Parameter('age', 'int', null, true),
                [
                    'numeric' => true,
                    'invalid' => null,
                    'valid' => 42
                ]
            ],
            'float parameter' => [
                new Parameter('height', 'float', null, true),
                [
                    'numeric' => true,
                    'invalid' => 'tall',
                    'valid' => 1.83
                ]
            ],
            'boolean parameter' => [
                new Parameter('human', 'boolean', true, true),
                [
                    'numeric' => false,
                    'invalid' => 'True',
                    'valid' => true
                ]
            ],
            'string parameter' => [
                new Parameter('name', 'string', null, true),
                [
                    'numeric' => false,
                    'invalid' => true,
                    'valid' => 'John'
                ]
            ],
            'class parameter' => [
                new Parameter('test', TestCase::class, null, false),
                [
                    'numeric' => false,
                    'invalid' => new stdClass(),
                    'valid' => $this
                ]
            ],
            'interface parameter' => [
                new Parameter('inspector', InspectorInterface::class, null, false),
                [
                    'numeric' => false,
                    'invalid' => 1000,
                    'valid' => new ReflectionInspector()
                ]
            ]
        ];
    }

    /**
     * @dataProvider exampleParameterProvider
     */
    public function testCanDetermineIfParameterIsNumeric(
        Parameter $parameter,
        array $details
    ): void {
        list('numeric' => $is_numeric) = $details;

        self::assertSame($is_numeric, $parameter->isNumeric());
    }

    /**
     * @dataProvider exampleParameterProvider
     */
    public function testCanTestIfValueWillValidate(
        Parameter $parameter,
        array $details
    ): void {
        list('valid' => $valid, 'invalid' => $invalid) = $details;

        self::assertTrue($parameter->validate($valid));
        self::assertFalse($parameter->validate($invalid));
    }

    public function testCanTakeIntForFloat(): void
    {
        $parameter = new Parameter('price', 'float', null, true);

        self::assertTrue($parameter->validate(20));
    }

    public function testCanTakeFloatForInt(): void
    {
        $parameter = new Parameter('area', 'int', null, true);

        self::assertTrue($parameter->validate(10.5));
    }

    public function testCanTakeNumericStringForInt(): void
    {
        $parameter = new Parameter('quantity', 'int', null, true);

        self::assertTrue($parameter->validate('12'));
    }

    public function testCanTakeNumericStringForFloat(): void
    {
        $parameter = new Parameter('height', 'float', null, true);

        self::assertTrue($parameter->validate('5.11'));
    }

    public function testCantTakeIntForFloatWhenStrict(): void
    {
        $parameter = new Parameter('price', 'float', null, true);

        self::assertFalse($parameter->validate(20, true));
    }

    public function testCantTakeFloatForIntWhenStrict(): void
    {
        $parameter = new Parameter('area', 'int', null, true);

        self::assertFalse($parameter->validate(10.5, true));
    }

    public function testCantTakeNumericStringForIntWhenStrict(): void
    {
        $parameter = new Parameter('quantity', 'int', null, true);

        self::assertFalse($parameter->validate('12', true));
    }

    public function testCantTakeNumericStringForFloatWhenStrict(): void
    {
        $parameter = new Parameter('height', 'float', null, true);

        self::assertFalse($parameter->validate('5.11', true));
    }
}
