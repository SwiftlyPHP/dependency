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

/**
 * @param int|float $value Example union param
 */
function exampleUnion(int|float $value): void {}
/**
 * @param mixed $value Example mixed param
 */
function exampleMixed(mixed $value): void {}
