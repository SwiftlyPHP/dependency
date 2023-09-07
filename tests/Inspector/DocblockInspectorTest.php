<?php

namespace Swiftly\Dependency\Tests\Inspector;

use Swiftly\Dependency\Tests\AbstractInspectorTest;
use Swiftly\Dependency\Inspector\DocblockInspector;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\Parameter\ArrayParameter;
use Swiftly\Dependency\Parameter\MixedParameter;
use Swiftly\Dependency\Exception\UndefinedFunctionException;
use Swiftly\Dependency\Exception\UndefinedClassException;
use Swiftly\Dependency\Exception\UndefinedMethodException;
use Swiftly\Dependency\Exception\CompoundTypeException;
use Swiftly\Dependency\Exception\UnknownTypeException;
use ExampleClass;

/**
 * @covers \Swiftly\Dependency\Inspector\DocblockInspector
 * @uses \Swiftly\Dependency\Parameter
 * @uses \Swiftly\Dependency\Parameter\ArrayParameter
 * @uses \Swiftly\Dependency\Parameter\BooleanParameter
 * @uses \Swiftly\Dependency\Parameter\MixedParameter
 * @uses \Swiftly\Dependency\Parameter\NamedClassParameter
 * @uses \Swiftly\Dependency\Parameter\NumericParameter
 * @uses \Swiftly\Dependency\Parameter\ObjectParameter
 * @uses \Swiftly\Dependency\Parameter\StringParameter
 * @uses \Swiftly\Dependency\Type
 */
final class DocblockInspectorTest extends AbstractInspectorTest
{
    private DocblockInspector $inspector;

    public function setUp(): void
    {
        $this->inspector = new DocblockInspector();
    }

    /**
     * @dataProvider exampleFunctionTrait
     * @testdox Can inspect single parameter of type $_dataName
     * @param callable $function                 Function to inspect
     * @param string $name                       Expected parameter name
     * @param class-string<Parameter> $classname Expected parameter class
     * @param string $type                       Expected parameter type
     */
    public function testCanInspectSingleParameter(callable $function, string $name, string $classname, string $type): void
    {
        list($parameter) = $this->inspector->inspectFunction($function);

        $expected = self::expectedParam($name, $classname, $type);

        self::assertParameter($expected, $parameter);
    }

    /**
     * @php:8.0 Delete this and uncomment entry in exampleTypeProvider
     * @testdox Can inspect single parameter of type mixed
     * @requires PHP >= 8.0
     */
    public function testCanInspectSingleMixedParameter(): void
    {
        $this->testCanInspectSingleParameter('exampleMixed', 'value', MixedParameter::class, 'mixed');
    }

    public function testCanInspectNullableParameter(): void
    {
        list($parameter) = $this->inspector->inspectFunction('exampleNullable');

        $expected = self::expectedParam('value', ArrayParameter::class, 'array');

        self::assertParameter($expected, $parameter);
        self::assertTrue($parameter->isNullable());
    }

    /**
     * @dataProvider exampleClassProvider
     * @testdox Can inspect constructor of $_dataName
     */
    public function testCanInspectConstructorParameters(string $classname, array $expected): void
    {
        $parameters = $this->inspector->inspectClass($classname);

        self::assertParameters($expected, $parameters);
    }

    /**
     * @dataProvider exampleMethodProvider
     * @testdox Can inspect $example->$_dataName method parameters
     */
    public function testCanInspectInstanceMethodParameters(string $method, array $expected): void
    {
        $class = new ExampleClass($this);

        $parameters = $this->inspector->inspectMethod($class, $method);

        self::assertParameters($expected, $parameters);
    }

    /**
     * @dataProvider exampleMethodProvider
     * @testdox Can inspect ExampleClass::$_dataName method parameters
     */
    public function testCanInspectClassnameMethodParameters(string $method, array $expected): void
    {
        $class = ExampleClass::class;

        $parameters = $this->inspector->inspectMethod($class, $method);

        self::assertParameters($expected, $parameters);
    }

    public function testCanRecoverIfDocblockMalformed(): void
    {
        $parameters = $this->inspector->inspectFunction('exampleMalformedDocblock');

        self::assertSame([], $parameters);
    }

    public function testCanRecoverIfDocblockMissing(): void
    {
        $parameters = $this->inspector->inspectFunction('exampleNoDocblock');

        self::assertSame([], $parameters);
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedFunctionException
     */
    public function testThrowsIfFunctionDoesNotExist(): void
    {
        self::expectException(UndefinedFunctionException::class);

        $this->inspector->inspectFunction('my_function');
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedClassException
     */
    public function testThrowsIfClassDoesNotExist(): void
    {
        self::expectException(UndefinedClassException::class);

        $this->inspector->inspectClass('UnknownClass');
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedClassException
     * @testdox Throws if class does not exist (when resolving method)
     */
    public function testThrowsIfClassDoesNotExistWhenResolvingMethod(): void
    {
        self::expectException(UndefinedClassException::class);

        $this->inspector->inspectMethod('UnknownClass', 'unknownMethod');
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedMethodException
     */
    public function testThrowsIfMethodDoesNotExist(): void
    {
        self::expectException(UndefinedMethodException::class);

        $this->inspector->inspectMethod($this, 'unknownMethod');
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UnknownTypeException
     */
    public function testThrowsIfParameterExpectsUnknownClass(): void
    {
        self::expectException(UnknownTypeException::class);

        $this->inspector->inspectFunction('exampleUnknown');
    }

    /**
     * @covers \Swiftly\Dependency\Exception\CompoundTypeException
     * @requires PHP >= 8.0
     */
    public function testThrowsIfCompoundType(): void
    {
        self::expectException(CompoundTypeException::class);

        $this->inspector->inspectMethod(\Php8Example::class, 'unionType');
    }
}
