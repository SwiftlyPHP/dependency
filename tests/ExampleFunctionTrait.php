<?php declare(strict_types=1);

namespace Swiftly\Dependency\Tests;

use Iterator;
use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Parameter\ArrayParameter;
use Swiftly\Dependency\Parameter\BooleanParameter;
use Swiftly\Dependency\Parameter\MixedParameter;
use Swiftly\Dependency\Parameter\NamedClassParameter;
use Swiftly\Dependency\Parameter\NumericParameter;
use Swiftly\Dependency\Parameter\ObjectParameter;
use Swiftly\Dependency\Parameter\StringParameter;
use Swiftly\Dependency\Tests\AbstractInspectorTest;

/**
 * Trait used to provide example functions.
 *
 * @mixin AbstractInspectorTest
 *
 * @upgrade:phpunit10 Refactor to #[DataProviderExternal]
 */
trait ExampleFunctionTrait
{
    public function exampleFunctionTrait(): array
    {
        return [
            'array' => [
                'exampleArray',
                ...self::expectedParam(
                    'value',
                    ArrayParameter::class,
                    'array'
                )
            ],
            'bool' => [
                'exampleBool',
                ...self::expectedParam(
                    'value',
                    BooleanParameter::class,
                    'bool'
                )
            ],
            'mixed' => [
                'exampleMixed',
                ...self::expectedParam(
                    'value',
                    MixedParameter::class,
                    'mixed'
                )
            ],
            'classname' => [
                'exampleClass',
                ...self::expectedParam(
                    'value',
                    NamedClassParameter::class,
                    TestCase::class
                )
            ],
            'interface' => [
                'exampleInterface',
                ...self::expectedParam(
                    'value',
                    NamedClassParameter::class,
                    Iterator::class
                )
            ],
            'int' => [
                'exampleInt',
                ...self::expectedParam(
                    'value',
                    NumericParameter::class,
                    'int'
                )
            ],
            'float' => [
                'exampleFloat',
                ...self::expectedParam(
                    'value',
                    NumericParameter::class,
                    'float'
                )
            ],
            'object' => [
                'exampleObject',
                ...self::expectedParam(
                    'value',
                    ObjectParameter::class,
                    'object'
                )
            ],
            'string' => [
                'exampleString',
                ...self::expectedParam(
                    'value',
                    StringParameter::class,
                    'string'
                )
            ],
            'any' => [
                'exampleAny',
                ...self::expectedParam(
                    'value',
                    MixedParameter::class,
                    'mixed'
                )
            ]
        ];
    }
}

/* Require the function definitions */
require_once __DIR__ . '/example/functions.inc';
