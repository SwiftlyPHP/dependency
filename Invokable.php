<?php

namespace Swiftly\Dependency;

/**
 *
 */
Class Invokable
{

    const TYPE_UNKNOWN = '';
    const TYPE_FUNCTION = '';
    const TYPE_CLOSURE = '';
    const TYPE_STATIC = '';
    const TYPE_METHOD = '';
    const TYPE_OBJECT = '';

    /**
     * The type of the underlying callable
     *
     * @var string $type Invokable type
     */
    private $type;

    /**
     *
     */
    private $callable;

    /**
     *
     */
    public function invoke() // : mixed

}
