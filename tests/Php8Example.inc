<?php
/**
 * Included for tests on environments where PHP >= 8
 * 
 * @see \Swiftly\Dependency\Tests\Resolver\ReflectionResolverTest::testThrowsIfCompoundType()
 *
 * @requires PHP >= 8.0
 * @internal
 */

final class Php8Example
{
    public function setValue(int|float $value): void {}
}
