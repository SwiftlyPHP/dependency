<?php
/**
 * Example functions used by Inspector tests
 *
 * The docblock params are not a mistake, they are used when testing the
 * {@see DocblockInspector}.
 *
 * @see \Swiftly\Dependency\Tests\Inspector\ReflectionInspectorTest
 * @see \Swiftly\Dependency\Tests\Inspector\DocblockInspectorTest
 *
 * @internal
 */

/**
 * @param array $value Example array value
 */
function exampleArray(array $value): void {}
/**
 * @param bool $value Example boolean value
 */
function exampleBool(bool $value): void {}
/**
 * @param PHPUnit\Framework\TestCase $value Example class param
 */
function exampleClass(PHPUnit\Framework\TestCase $value): void {}
/**
 * @param Iterator $value Example interface param
 */
function exampleInterface(Iterator $value): void {}
/**
 * @param int $value Example int param
 */
function exampleInt(int $value): void {}
/**
 * @param float $value Example float param
 */
function exampleFloat(float $value): void {}
/**
 * @param object $value Example object param
 */
function exampleObject(object $value): void {}
/**
 * @param string $value Example string param
 */
function exampleString(string $value): void {}
/**
 * @param ?array $value Example (nullable) array value
 */
function exampleNullable(?array $value): void {}
/**
 * @param string $value Example string value (with default)
 */
function exampleDefault(string $value = 'Hi!'): void {}
/**
 * @param MyClass $value Example undefined class param
 */
function exampleUnknown(MyClass $value): void {}

/**
 * File contains union and mixed type hints, only include on PHP >= 8
 *
 * @php:8.0 Move these functions into this file
 */
if (PHP_MAJOR_VERSION >= 8) require_once __DIR__ . '/php8-functions.inc';
