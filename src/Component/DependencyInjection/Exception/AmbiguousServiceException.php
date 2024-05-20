<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection\Exception;

use function implode;
use function sprintf;

final class AmbiguousServiceException extends RuntimeException
{
    public static function forType(string $type, array $serviceIds): self
    {
        return new self(sprintf(
            'Multiple services of type "%s" found: %s',
            $type,
            implode(', ', $serviceIds)
        ));
    }
}
