<?php

namespace Swiftly\Dependency\Tests;

use Swiftly\Dependency\Tests\AbstractInspectorTest;
use Swiftly\Dependency\Parameter\ArrayParameter;
use Swiftly\Dependency\Parameter\NumericParameter;
use Swiftly\Dependency\Parameter\StringParameter;
use Swiftly\Dependency\Parameter\BooleanParameter;

/**
 * Trait used to provide example methods
 *
 * @mixin AbstractInspectorTest
 */
trait ExampleMethodTrait
{
    public function exampleMethodProvider(): array
    {
        return [
            'exampleMethod()' => [
                'exampleMethod',
                [
                    self::expectedParam('value1', NumericParameter::class, 'int'),
                    self::expectedParam('value2', ArrayParameter::class, 'array')
                ]
            ],
            'exampleStatic()' => [
                'exampleStatic',
                [
                    self::expectedParam('value1', StringParameter::class, 'string'),
                    self::expectedParam('value2', BooleanParameter::class, 'bool')
                ]
            ]
        ];
    }
}

/* Require the class definitions */
require_once __DIR__ . '/example/methods.inc';
