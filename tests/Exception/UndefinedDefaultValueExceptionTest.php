<?php

namespace Swiftly\Dependency\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Exception\UndefinedDefaultValueException;

/**
 * All `ParameterException` types are covered by our API guarantee
 *
 * @covers \Swiftly\Dependency\ParameterException
 * @covers \Swiftly\Dependency\Exception\UndefinedDefaultValueException
 */
final class UndefinedDefaultValueExceptionTest extends TestCase
{
    private UndefinedDefaultValueException $exception;

    public function setUp(): void
    {
        $this->exception = new UndefinedDefaultValueException('name');
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
}
