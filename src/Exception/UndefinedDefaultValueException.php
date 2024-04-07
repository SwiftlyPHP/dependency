<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use Swiftly\Dependency\ParameterException;

use function sprintf;

/**
 * Exception used to indicate a parameter has no default value
 *
 * @api
 */
final class UndefinedDefaultValueException extends ParameterException
{
    /**
     * Indicate a parameter does not have a default value
     *
     * @param non-empty-string $parameter Parameter name
     */
    public function __construct(string $parameter)
    {
        $this->parameter = $parameter;

        parent::__construct(
            sprintf(
                "Could not determine a default value for parameter '\$%s'",
                $parameter
            )
        );
    }
}
