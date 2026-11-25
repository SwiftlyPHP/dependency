<?php declare(strict_types=1);

namespace Swiftly\Dependency\Exception;

use LogicException;
use Swiftly\Dependency\Parameter;
use Swiftly\Dependency\ProvideInterface;

use function get_debug_type;
use function sprintf;

/**
 * Indicates that a user defined `#[ProviderInterface]` attribute is invalid.
 *
 * @api
 */
final class AttributeException extends LogicException
{
    public static function typeError(
        ProvideInterface $provider,
        Parameter $parameter,
        mixed $given,
    ): self {
        return new self(sprintf(
            'The #[%s] attribute for parameter $%s is incorrect. The parameter'
            . ' expects %s but the attribute provided a %s instead.',
            $provider::class,
            $parameter->name,
            $parameter->type->getName(),
            get_debug_type($given),
        ));
    }
}
