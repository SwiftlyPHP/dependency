<?php

namespace Swiftly\Dependency\Exception;

use Swiftly\Dependency\Exception\NestedServiceException;
use RuntimeException;
use Exception;

use function sprintf;
use function str_repeat;
use function implode;

/**
 * Exception used to indicate an error occured while instantiating a service
 *
 * @api
 */
final class ServiceInstantiationException extends RuntimeException
{
    /**
     * Indicate an error occured while creating this service
     *
     * @param non-empty-string $service Service name
     * @param Exception $reason         Failure reason
     */
    public function __construct(string $service, Exception $reason)
    {
        parent::__construct(
            sprintf(
                "Encountered an error while resolving service '%s':\n%s",
                $service,
                self::unwrapReason($reason)
            ),
            0,
            $reason
        );
    }

    /**
     * Unwrap the exception stack and generate a readable string
     * 
     * @param Exception $reason Top-most reason
     * @return string           Reason message
     */
    private static function unwrapReason(Exception $reason): string
    {
        $depth = 0;
        $messages = [];

        do {
            if ($reason instanceof NestedServiceException) {
                $depth++;
                continue;
            }

            $messages[] = str_repeat("\t", $depth) . $reason->getMessage();
        } while (($reason = $reason->getPrevious()) !== null);

        return implode("\t", $messages);
    }
}
