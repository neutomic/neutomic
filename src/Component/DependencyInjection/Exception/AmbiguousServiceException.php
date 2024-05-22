<?php

declare(strict_types=1);

/*
 * This file is part of the Neutomic package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neu\Component\DependencyInjection\Exception;

use Psl\Str;

final class AmbiguousServiceException extends RuntimeException
{
    /**
     * Create a new ambiguous service exception.
     *
     * @param non-empty-string $type The type of the services.
     * @param list<non-empty-string> $serviceIds An array of container services identifiers.
     */
    public static function forType(string $type, array $serviceIds): self
    {
        return new self('Multiple services of type "' . $type . '" found: ' . Str\join($serviceIds, ', '));
    }
}
