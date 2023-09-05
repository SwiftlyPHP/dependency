<?php

namespace Swiftly\Dependency\Tests\Inspector
{

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Inspector\ReflectionInspector;
use Swiftly\Dependency\Parameter;
use Closure;
use ReflectionClass;
use Swiftly\Dependency\Exception\UndefinedFunctionException;
use Swiftly\Dependency\Exception\UndefinedClassException;
use Swiftly\Dependency\Exception\UndefinedMethodException;
use Swiftly\Dependency\Exception\CompoundTypeException;

use function count;
use function dirname;

/**
 * @covers \Swiftly\Dependency\Inspector\ReflectionInspector
 * @uses \Swiftly\Dependency\Parameter
 */
final class ReflectionInspectorTest extends TestCase
{
    private ReflectionInspector $inspector;

    public function setUp(): void
    {
        $this->inspector = new ReflectionInspector();
    }

    public function exampleFunctionProvider(): array
    {
        return [
            'example function 1' => [
                'example_function_1',
                [
                    ['name', 'string'],
                    ['age', 'int']
                ]
            ],
            'example function 2' => [
                'example_function_2',
                [
                    ['email', 'string'],
                    ['is_admin', 'bool']
                ]
            ],
            'example function 3' => [
                'example_function_3',
                [
                    ['name', 'string'],
                    ['test', TestCase::class],
                    ['timeout', 'float'],
                ]
            ]
        ];
    }

    public function exampleClassProvider(): array
    {
        return [
            'std class' => [
                \stdClass::class,
                []
            ],
            'example class' => [
                \ExampleClass::class,
                [
                    ['name', 'string'],
                    ['start', \DateTime::class],
                    ['length', \DateInterval::class]
                ]
            ],
            'derived class' => [
                \DerivedClass::class,
                [
                    ['name', 'string'],
                    ['start', \DateTime::class],
                    ['length', \DateInterval::class]
                ]
            ]
        ];
    }

    public function exampleMethodProvider(): array
    {
        return [
            'ExampleClass::method1()' => [
                \ExampleClass::class,
                'method1',
                [
                    ['duration', \DateInterval::class],
                    ['changelog', 'array']
                ]
            ],
            'DerivedClass::method1()' => [
                \DerivedClass::class,
                'method1',
                [
                    ['duration', \DateInterval::class],
                    ['changelog', 'array']
                ]

            ],
            'DerivedClass::method2()' => [
                \DerivedClass::class,
                'method2',
                [
                    ['updates', \stdClass::class],
                    ['reason', 'string']
                ]
            ]
        ];
    }

    private function checkParameters(array $expected, array $actual): void
    {
        self::assertCount(count($expected), $actual);
        self::assertContainsOnlyInstancesOf(Parameter::class, $actual);
        foreach ($expected as $index => [$name, $type]) {
            self::assertSame($name, $actual[$index]->name);
            self::assertSame($type, $actual[$index]->type);
        }
    }

    /**
     * @dataProvider exampleFunctionProvider
     */
    public function testCanResolveFunctionByName(
        string $function,
        array $parameters
    ): void {
        $resolved = $this->inspector->inspectFunction($function);

        self::checkParameters($parameters, $resolved);
    }

    /**
     * @dataProvider exampleFunctionProvider
     */
    public function testCanResolveClosure(
        string $function,
        array $parameters
    ): void {
        $resolved = $this->inspector->inspectFunction(
            Closure::fromCallable($function)
        );

        self::checkParameters($parameters, $resolved);
    }

    /**
     * @dataProvider exampleClassProvider
     */
    public function testCanResolveClass(string $class, array $parameters): void
    {
        $resolved = $this->inspector->inspectClass($class);

        self::checkParameters($parameters, $resolved);
    }

    /**
     * @dataProvider exampleMethodProvider
     * @testdox Can resolve method (class name)
     */
    public function testCanResolveMethodClassName(
        string $classname,
        string $method,
        array $parameters
    ): void {
        $resolved = $this->inspector->inspectMethod($classname, $method);

        self::checkParameters($parameters, $resolved);
    }

    /**
     * @dataProvider exampleMethodProvider
     * @testdox Can resolve method (class instance)
     */
    public function testCanResolveMethodClassInstance(
        string $classname,
        string $method,
        array $parameters
    ): void {
        $resolved = $this->inspector->inspectMethod(
            (new ReflectionClass($classname))->newInstanceWithoutConstructor(),
            $method
        );

        self::checkParameters($parameters, $resolved);
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedFunctionException
     */
    public function testThrowsIfFunctionDoesNotExist(): void
    {
        self::expectException(UndefinedFunctionException::class);

        $this->inspector->inspectFunction('example_function_4');
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedClassException
     */
    public function testThrowsIfClassDoesNotExist(): void
    {
        self::expectException(UndefinedClassException::class);

        $this->inspector->inspectClass('MyNewClass');
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedClassException
     * @testdox Throws if class does not exist (when resolving method)
     */
    public function testThrowsIfClassDoesNotExistWhenResolvingMethod(): void
    {
        self::expectException(UndefinedClassException::class);

        $this->inspector->inspectMethod('MyNewClass', 'someMethod');
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedMethodException
     */
    public function testThrowsIfMethodDoesNotExist(): void
    {
        self::expectException(UndefinedMethodException::class);

        $this->inspector->inspectMethod(\ExampleClass::class, 'someMethod');
    }

    /**
     * @covers \Swiftly\Dependency\Exception\CompoundTypeException
     * @requires PHP >= 8.0
     */
    public function testThrowsIfCompoundType(): void
    {
        self::expectException(CompoundTypeException::class);
        
        // Tests union/intersection types so only include file if PHP >= 8
        require_once dirname(__DIR__) . '/Php8Example.inc';

        $this->inspector->inspectMethod(\Php8Example::class, 'setValue');
    }
}
};

/**
 * Example functions for the tests above
 */
namespace {
    use PHPUnit\Framework\TestCase;

    function example_function_1(string $name, int $age) {}
    function example_function_2(string $email, bool $is_admin) {}
    function example_function_3(string $name, TestCase $test, float $timeout) {}

    class ExampleClass
    {
        public function __construct(string $name, DateTime $start, DateInterval $length) {}
        public function method1(DateInterval $duration, array $changelog) {}
    }

    class DerivedClass extends ExampleClass
    {
        public function method2(stdClass $updates, ?string $reason = null) {}
    }
}
