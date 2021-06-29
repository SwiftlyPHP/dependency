<?php

namespace Swiftly\Dependency;

/**
 * Used to represent the types a callable can be
 *
 * When we get to PHP 8.1 we can swap to enums, but until then, this class will
 * have to do!
 *
 * @author clvarley
 */
Class CallableType
{

    /**
     * Indicates the callable is of an unknown type
     *
     * @var int TYPE_UNKNOWN Invalid callable
     */
    const TYPE_UNKNOWN = 0;

    /**
     * Indicates the callable is a standard function
     *
     * @var int TYPE_FUNCTION Standard function
     */
    const TYPE_FUNCTION = 1;

    /**
     * Indicates the callable is a closure
     *
     * @var int TYPE_CLOSURE Closure function
     */
    const TYPE_CLOSURE = 2;

    /**
     * Indicates the callable is a static method
     *
     * @var int TYPE_STATIC Static function
     */
    const TYPE_STATIC = 3;

    /**
     * Indicates the callable is a class method
     *
     * @var int TYPE_METHOD Class method
     */
    const TYPE_METHOD = 4;

    /**
     * Indicates the callable is an invokable object
     *
     * @var int TYPE_OBJECT Invokable object
     */
    const TYPE_OBJECT = 5;

    /**
     * Indicates the callable is actually a class constructor
     *
     * @var int TYPE_CONSTRUCT Class constructor
     */
    const TYPE_CONSTRUCT = 6;

}
