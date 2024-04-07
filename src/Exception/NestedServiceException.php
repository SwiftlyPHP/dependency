<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use RuntimeException;
use Exception;

/**
 * Wrapper user to let us pass exception information to the top-most scope
 *
 * @internal
 */
final class NestedServiceException extends RuntimeException
{
    /**
     * @param Exception $reason Failure reason
     */
    public function __construct(Exception $reason)
    {
        parent::__construct('', 0, $reason);
    }
}
