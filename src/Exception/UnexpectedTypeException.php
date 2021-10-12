<?php

namespace Swiftly\Dependency\Exception;

use UnexpectedValueException;

use function sprintf;
use function is_object;
use function get_class;
use function gettype;

/**
 * Exception thrown if a resolved service is not of the expected type
 *
 * @template TActual
 *
 * @author clvarley
 */
Final Class UnexpectedTypeException Extends UnexpectedValueException
{

    /**
     * @psalm-var TActual
     *
     * @var mixed $actual
     */
    private $actual;

    /**
     * @psalm-param class-string $expected
     * @psalm-param TActual $actual
     *
     * @param string $expected Expected class
     * @param mixed $actual    Actual result
     */
    public function __construct( string $expected, $actual )
    {
        $this->actual;

        parent::__construct(
            sprintf(
                'Container expected to resolve a dependency of type (%s), but instead resolved (%s)',
                $expected,
                is_object( $actual ) ? get_class( $actual ) : gettype( $actual )
            )
        );
    }

    /**
     * @psalm-return TActual
     *
     * @return mixed Actual result
     */
    public function getActual() // : mixed
    {
        return $this->actual;
    }
}
