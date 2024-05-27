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

namespace Neu\Component\Broadcast\DependencyInjection\Factory\Transport;

use Neu\Component\Broadcast\Transport\LocalTransport;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * @implements FactoryInterface<LocalTransport>
 */
final readonly class LocalTransportFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new LocalTransport();
    }
}
