<?php
/**
 * Included for tests on environments where PHP >= 8
 * 
 * @see \Swiftly\Dependency\Tests\Inspector\ReflectionInspectorTest::testCanInspectMixedType()
 * @see \Swiftly\Dependency\Tests\Inspector\ReflectionInspectorTest::testThrowsIfCompoundType()
 *
 * @requires PHP >= 8.0
 * @internal
 */

final class Php8Example
{
    public function unionType(int|float $value): void {}
    public function mixedType(mixed $value): void {}
}
