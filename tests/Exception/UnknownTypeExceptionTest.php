<?php

namespace Swiftly\Dependency\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Exception\UnknownTypeException;

/**
 * All `ParameterException` types are covered by our API guarantee
 *
 * @covers \Swiftly\Dependency\ParameterException
 * @covers \Swiftly\Dependency\Exception\UnknownTypeException
 */
final class UnknownTypeExceptionTest extends TestCase
{
    private UnknownTypeException $exception;

    public function setUp(): void
    {
        $this->exception = new UnknownTypeException('name', 'UnknownClass');
    }

    public function testCanGetParameterName(): void
    {
        self::assertSame('name', $this->exception->getParameterName());
    }

    public function testCanGetParameterType(): void
    {
        self::assertSame('UnknownClass', $this->exception->getType());
    }

    public function testOutputShouldIncludeParameterNameOnThrow(): void
    {
        self::expectExceptionMessageMatches('/\$name/');

        throw $this->exception;
    }
}
