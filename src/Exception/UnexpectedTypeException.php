<?php

namespace Swiftly\Dependency\Exception;

use UnexpectedValueException;

use function sprintf;

/**
 * Exception thrown if a resolved service is not of the expected type
 *
 * @author clvarley
 */
Class UnexpectedTypeException Extends UnexpectedValueException
{

    /**
     * @psalm-param class-string $expected
     *
     * @param string $expected Expected class
     * @param mixed $actual    Actual result
     */
    public function __construct( string $expected, $actual )
    {
        parent::__construct(
            sprintf(
                'Container expected to resolve a dependency of type %s, but instead resolved a %s',
                $expected,
                get_class( $actual )
            )
        );
    }
}
