<?php

namespace Swiftly\Dependency\Tests;

use Swiftly\Dependency\Tests\AbstractInspectorTest;
use Swiftly\Dependency\Parameter\NamedClassParameter;
use Swiftly\Dependency\Parameter\ObjectParameter;
use Swiftly\Dependency\Parameter\NumericParameter;
use PHPUnit\Framework\TestCase;
use ExampleClass;
use FakeClass;
use stdClass;

/**
 * Trait used to provide example class definitions
 *
 * @mixin AbstractInspectorTest
 */
trait ExampleClassTrait
{
    public function exampleClassProvider(): array
    {
        return [
            'ExampleClass' => [
                ExampleClass::class,
                [
                    self::expectedParam('value', NamedClassParameter::class, TestCase::class)
                ]
            ],
            'FakeClass' => [
                FakeClass::class,
                [
                    self::expectedParam('value1', ObjectParameter::class, 'object'),
                    self::expectedParam('value2', NumericParameter::class, 'float')
                ]
            ],
            'stdClass' => [
                stdClass::class,
                []
            ]
        ];
    }
}

/* Require the class definitions */
require_once __DIR__ . '/example/methods.inc';
