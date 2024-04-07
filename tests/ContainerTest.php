<?php

namespace Swiftly\Dependency\Tests;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Container;
use Swiftly\Dependency\Inspector\ReflectionInspector;
use Swiftly\Dependency\InspectorInterface;
use Swiftly\Dependency\Exception\UndefinedServiceException;
use Swiftly\Dependency\Exception\ServiceInstantiationException;
use Swiftly\Dependency\Exception\UnexpectedTypeException;

/**
 * @covers \Swiftly\Dependency\Container
 * @uses \Swiftly\Dependency\Inspector\ReflectionInspector
 * @uses \Swiftly\Dependency\Entry
 * @uses \Swiftly\Dependency\Type
 * @uses \Swiftly\Dependency\Parameter
 * @uses \Swiftly\Dependency\Parameter\StringParameter
 * @uses \Swiftly\Dependency\Parameter\NumericParameter
 * @uses \Swiftly\Dependency\Parameter\NamedClassParameter
 */
final class ContainerTest extends TestCase
{
    private Container $container;

    public function setUp(): void
    {
        $this->container = new Container(new ReflectionInspector);
    }

    public function exampleMethod(): object
    {
        return new ReflectionInspector;
    }

    public function exampleServiceProvider(): array
    {
        return [
            'classname' => [
                ReflectionInspector::class,
                null
            ],
            'closure' => [
                InspectorInterface::class,
                fn() => new ReflectionInspector()
            ],
            'method' => [
                InspectorInterface::class,
                [$this, 'exampleMethod']
            ],
            'instance' => [
                TestCase::class,
                $this
            ]
        ];
    }

    /**
     * @dataProvider exampleServiceProvider
     */
    public function testCanRegisterService(string $service, $factory_or_instance): void
    {
        $this->container->register($service, $factory_or_instance);

        self::assertTrue($this->container->has($service));
        self::assertInstanceOf($service, $this->container->get($service));
    }

    public function testCanPassArgumentsToFactory(): void
    {
        $this->container->register(
            TestCase::class,
            function (string $name, int $number) {
                self::assertSame('John', $name);
                self::assertSame(42, $number);
                return $this;
            }
        )->setArguments([
            'name' => 'John',
            'number' => 42
        ]);

        $this->container->get(TestCase::class);
    }

    public function testCanPassDefaultArgumentsToFactory(): void
    {
        $this->container->register(
            TestCase::class,
            function (string $name, int $number = 42) {
                self::assertSame('John', $name);
                self::assertSame(42, $number);
                return $this;
            }
        )->setArguments([
            'name' => 'John'
        ]);

        $this->container->get(TestCase::class);
    }

    public function testCanPassNullableArgumentsToFactory(): void
    {
        $this->container->register(
            TestCase::class,
            function (string $name, ?int $number) {
                self::assertSame('John', $name);
                self::assertNull($number);
                return $this;
            }
        )->setArguments([
            'name' => 'John'
        ]);

        $this->container->get(TestCase::class);
    }

    public function testCanPassInferredArgumentsToFactory(): void
    {
        $this->container->register(Container::class, $this->container);
        $this->container->register(ReflectionInspector::class);

        $this->container->register(
            TestCase::class,
            function (Container $container, ReflectionInspector $inspector) {
                self::assertSame($this->container, $container);
                self::assertInstanceOf(Container::class, $container);
                self::assertInstanceOf(ReflectionInspector::class, $inspector);
                return $this;
            }
        );

        $this->container->get(TestCase::class);
    }

    public function testCanAliasRegisteredService(): void
    {
        $this->container->register(self::class, $this);
        $this->container->alias(self::class, TestCase::class);

        self::assertTrue($this->container->has(TestCase::class));
        self::assertSame($this, $this->container->get(TestCase::class));
    }

    public function testCanGetAllTaggedServices(): void
    {
        $this->container->register(TestCase::class, $this)
            ->setTags(['tests']);
        $this->container->register(self::class, $this)
            ->setTags(['tests']);
        $this->container->register(Container::class)
            ->setTags(['dependency']);

        $resolved = $this->container->tagged('tests');

        self::assertIsArray($resolved);
        self::assertCount(2, $resolved);
        self::assertContainsOnlyInstancesOf(TestCase::class, $resolved);
    }

    public function testCanGetCachedService(): void
    {
        $this->container->register(ReflectionInspector::class);

        $service1 = $this->container->get(ReflectionInspector::class);
        $service2 = $this->container->get(ReflectionInspector::class);

        self::assertSame($service1, $service2);
    }

    public function testCanDisableServiceCaching(): void
    {
        $this->container->register(ReflectionInspector::class)->setOnce(false);

        $service1 = $this->container->get(ReflectionInspector::class);
        $service2 = $this->container->get(ReflectionInspector::class);

        self::assertNotSame($service1, $service2);
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedServiceException
     */
    public function testThrowsIfAliasingUndefinedService(): void
    {
        self::expectException(UndefinedServiceException::class);

        $this->container->alias(ReflectionInspector::class, InspectorInterface::class);
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UndefinedServiceException
     */
    public function testThrowsIfRequestingUndefinedService(): void
    {
        self::expectException(UndefinedServiceException::class);

        $this->container->get(ReflectionInspector::class);
    }

    /**
     * @covers \Swiftly\Dependency\Exception\MissingArgumentException
     * @covers \Swiftly\Dependency\Exception\ServiceInstantiationException
     */
    public function testThrowsIfRequiredArgumentMissing(): void
    {
        self::expectException(ServiceInstantiationException::class);
        self::expectExceptionMessageMatches('/\$value/');

        $this->container->register(
            TestCase::class,
            static function (string $value) {}
        );
        $this->container->get(TestCase::class);
    }

    /**
     * @covers \Swiftly\Dependency\Exception\InvalidArgumentException
     * @covers \Swiftly\Dependency\Exception\ServiceInstantiationException
     */
    public function testThrowsIfProvidedArgumentIsWrongType(): void
    {
        self::expectException(ServiceInstantiationException::class);
        self::expectExceptionMessageMatches('/\$value/');

        $this->container->register(
            TestCase::class,
            static function (string $value) {}
        )->setArguments([
            'value' => ['Hi!']
        ]);
        $this->container->get(TestCase::class);
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UnexpectedTypeException
     */
    public function testThrowsIfResolvedServiceIsWrongType(): void
    {
        self::expectException(UnexpectedTypeException::class);

        $this->container->register(InspectorInterface::class, $this);
        $this->container->get(InspectorInterface::class);
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UnexpectedTypeException
     * @covers \Swiftly\Dependency\Exception\NestedServiceException
     * @covers \Swiftly\Dependency\Exception\ServiceInstantiationException
     */
    public function testThrowsOnNestedFailure(): void
    {
        self::expectException(ServiceInstantiationException::class);
        self::expectExceptionMessageMatches('/InspectorInterface/');

        // Container requires InspectorInterface but will resolve wrong type
        $this->container->register(Container::class);
        $this->container->register(InspectorInterface::class, $this);
        $this->container->get(Container::class);
    }

    /**
     * @covers \Swiftly\Dependency\Exception\UnexpectedTypeException
     */
    public function testThrowsIfTaggedServicesBreakConstaint(): void
    {
        self::expectException(UnexpectedTypeException::class);

        $this->container->register(ReflectionInspector::class)
            ->setTags(['inspector']);
        $this->container->register(InspectorInterface::class, fn() => new ReflectionInspector)
            ->setTags(['inspector']);
        $this->container->register(TestCase::class, $this)
            ->setTags(['inspector']);

        $this->container->tagged('inspector', InspectorInterface::class);
    }
}
