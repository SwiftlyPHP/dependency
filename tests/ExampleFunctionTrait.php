<?php

namespace Swiftly\Dependency\Tests;

use Swiftly\Dependency\Tests\AbstractInspectorTest;
use Swiftly\Dependency\Parameter\ArrayParameter;
use Swiftly\Dependency\Parameter\BooleanParameter;
use Swiftly\Dependency\Parameter\MixedParameter;
use Swiftly\Dependency\Parameter\NamedClassParameter;
use Swiftly\Dependency\Parameter\NumericParameter;
use Swiftly\Dependency\Parameter\ObjectParameter;
use Swiftly\Dependency\Parameter\StringParameter;
use PHPUnit\Framework\TestCase;
use Iterator;

/**
 * Trait used to provide example functions
 *
 * @mixin AbstractInspectorTest
 */
trait ExampleFunctionTrait
{
    /** @php:8.0 Uncomment the `mixed` type test */
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
            /*
            'mixed' => [
                'exampleMixed',
                ...self::expectedParam(
                    'value',
                    MixedParameter::class,
                    'mixed'
                )
            ],
            */
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
