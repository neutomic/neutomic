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

use Psr\Container\NotFoundExceptionInterface;

final class ServiceNotFoundException extends RuntimeException implements ExceptionInterface, NotFoundExceptionInterface
{
    /**
     * Create a new instance for a service id.
     *
     * @param string $id The service id.
     */
    public static function forService(string $id): self
    {
        return new self('Service "' . $id . '" is not found.');
    }
}
