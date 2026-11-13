<?php declare(strict_types=1);

namespace Swiftly\Dependency\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swiftly\Dependency\Exception\UndefinedDefaultValueException;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\ParameterException;

/**
 * All `ParameterException` types are covered by our API guarantee.
 *
 */
#[CoversClass(ParameterException::class)]
#[CoversClass(UndefinedDefaultValueException::class)]
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
