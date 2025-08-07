<?php declare(strict_types=1);

namespace Swiftly\Dependency\Tests;

use Swiftly\Dependency\Parameter\ArrayParameter;
use Swiftly\Dependency\Parameter\BooleanParameter;
use Swiftly\Dependency\Parameter\NumericParameter;
use Swiftly\Dependency\Parameter\StringParameter;
use Swiftly\Dependency\Tests\AbstractInspectorTest;

/**
 * Trait used to provide example methods.
 *
 * @upgrade:phpunit10 Refactor to #[DataProviderExternal]
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
