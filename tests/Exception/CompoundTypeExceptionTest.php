<?php

namespace Swiftly\Dependency\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Exception\CompoundTypeException;
use ReflectionMethod;

/**
 * All `ParameterException` types are covered by our API guarantee
 *
 * @covers \Swiftly\Dependency\ParameterException
 * @covers \Swiftly\Dependency\Exception\CompoundTypeException
 */
final class CompoundTypeExceptionTest extends TestCase
{
    private CompoundTypeException $exception;

    public function setUp(): void
    {
        $this->exception = new CompoundTypeException('name', 'exampleFunc');
    }

    public function testCanGetParameterName(): void
    {
        self::assertSame('name', $this->exception->getParameterName());
    }

    public function testOutputShouldIncludeParameterNameOnThrow(): void
    {
        self::expectExceptionMessageMatches('/\$name/');

        throw $this->exception;
    }

    public function testOutputShouldIncludeFunctionNameOnThrow(): void
    {
        self::expectExceptionMessageMatches('/exampleFunc\(\)/');

        throw $this->exception;
    }

    public function testOutputShouldIncludeMethodNameOnThrow(): void
    {
        self::expectExceptionMessageMatches('/TestCase::setUp\(\)/');

        throw new CompoundTypeException('name', new ReflectionMethod(TestCase::class, 'setUp'));
    }
}
