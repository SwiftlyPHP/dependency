<?php

namespace Swiftly\Dependency\Exception;

use RuntimeException;

use function sprintf;

/**
 * Exception used to indicate the parsing of a docblock param failed
 *
 * @internal
 */
final class DocblockParseException extends RuntimeException
{
    private string $parameter;

    public function __construct(string $parameter)
    {
        $this->parameter = $parameter;

        parent::__construct(
            sprintf(
                "Could not parse docblock comment for parameter \$%s",
                $parameter
            )
        );
    }

    /**
     * Return the name of the parameter that caused this parse exception
     *
     * @return string Parameter name
     */
    public function getParameterName(): string
    {
        return $this->parameter;
    }
}
