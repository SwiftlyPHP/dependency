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

class ExampleClass
{
    /**
     * @param PHPUnit\Framework\TestCase $value Example class parameter
     */
    public function __construct(PHPUnit\Framework\TestCase $value) {}
    /**
     * @param int $value1   Example int parameter
     * @param array $value2 Example array parameter
     */
    public function exampleMethod(int $value1, array $value2): void {}
    /**
     * @param string $value1 Example string parameter
     * @param bool $value2   Example boolean parameter
     */
    public static function exampleStatic(string $value1, bool $value2): void {}
}

class FakeClass
{
    /**
     * @param object $value1 Example object parameter
     * @param float $value2  Example float parameter
     */
    public function __construct(object $value1, float $value2) {}
}
