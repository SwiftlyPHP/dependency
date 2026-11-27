<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use Attribute;
use LogicException;
use Swiftly\Dependency\Parameter;

use function get_debug_type;
use function sprintf;

/**
 * Indicates that a user configured `#[ProviderInterface]` attribute is invalid.
 *
 * @api
 */
final class AttributeException extends LogicException
{
    public static function typeError(
        Attribute $attribute,
        Parameter $parameter,
        mixed $given,
    ): self {
        return new self(sprintf(
            'The #[%s] attribute for parameter $%s is incorrect. The parameter'
            . ' expects %s but the attribute provided a %s instead.',
            $attribute::class,
            $parameter->name,
            (string) $parameter->type,
            get_debug_type($given),
        ));
    }
}
