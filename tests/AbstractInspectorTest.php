<?php

namespace Swiftly\Dependency\Tests;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Tests\ExampleFunctionTrait;
use Swiftly\Dependency\Tests\ExampleClassTrait;
use Swiftly\Dependency\Tests\ExampleMethodTrait;
use Swiftly\Dependency\Parameter;

/**
 * Shared functionality usefull for InspectorInterface testing
 *
 * @see \Swiftly\Dependency\Tests\Inspector\ReflectionInspectorTest
 * @see \Swiftly\Dependency\Tests\Inspector\DocblockInspectorTest
 *
 * @abstract
 */
abstract class AbstractInspectorTest extends TestCase
{
    use ExampleFunctionTrait;
    use ExampleClassTrait;
    use ExampleMethodTrait;

    /**
     * Create a parameter expectation
     *
     * Used in conjunction with {@see self::assertParameter()} to check
     * inspected parameters meet expectations.
     *
     * @param string $name
     * @param class-string<Parameter> $classname
     * @param string $type
     * @return array{0:string, 1:class-string<Parameter>, 2:string}
     */
    protected static function expectedParam(string $name, string $classname, string $type): array
    {
        return [$name, $classname, $type];
    }

    /**
     * Assert that the returned parameters match expections
     *
     * @param list<array{0:string, 1:class-string<Parameter>, 2:string}> $expected
     * @param list<Parameter> $actual
     */
    protected static function assertParameters(array $expected, array $actual): void
    {
        self::assertCount(count($expected), $actual);

        foreach ($expected as $i => $expect) {
            $parameter = $actual[$i];
            self::assertParameter($expect, $parameter);
        }
    }

    /**
     * Assert that a returned parameter matches expectations
     *
     * `$expected` should be an array like the following:
     * * 0 => parameter name
     * * 1 => parameter subclass
     * * 2 => parameter type
     *
     * @see self::expectedParam()
     *
     * @param array{0:string, 1:class-string<Parameter>, 2:string} $expected
     * @param Parameter $actual
     */
    protected static function assertParameter(array $expected, Parameter $actual): void
    {
        list($name, $classname, $type) = $expected;

        self::assertSame($name, $actual->getName());
        self::assertSame($type, $actual->getType());
        self::assertInstanceOf($classname, $actual);
    }
}
