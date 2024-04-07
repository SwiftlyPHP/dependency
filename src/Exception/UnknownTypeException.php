<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use Swiftly\Dependency\ParameterException;

use function sprintf;

/**
 * Exception used to indicate we cannot determine the type a parameter takes
 *
 * @api
 */
final class UnknownTypeException extends ParameterException
{
    private string $type;

    /**
     * Indicate we cannot handle the `$type` of this parameter
     *
     * @param non-empty-string $parameter Parameter name
     * @param string $type                Type name
     */
    public function __construct(string $parameter, string $type)
    {
        $this->parameter = $parameter;
        $this->type = $type;

        parent::__construct(
            sprintf(
                "Could not determine the type expected by parameter '\$%s'",
                $parameter
            )
        );
    }

    /**
     * Return the type name we encountered that caused this exception
     *
     * @return string Type name
     */
    public function getType(): string
    {
        return $this->type;
    }
}
