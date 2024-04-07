<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use Swiftly\Dependency\ParameterException;

use function sprintf;

/**
 * Exception used to indicate the parsing of a docblock param failed
 *
 * @internal
 */
final class DocblockParseException extends ParameterException
{
    /**
     * Indicate that a parsing error occurred while inspecting a parameter
     *
     * @param non-empty-string $parameter Parameter name
     */
    public function __construct(string $parameter)
    {
        $this->parameter = $parameter;

        parent::__construct(
            sprintf(
                'Could not parse docblock comment for parameter $%s',
                $parameter
            )
        );
    }
}
