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

namespace Neu\Component\Broadcast\DependencyInjection\Factory;

use Neu\Component\Broadcast\Hub;
use Neu\Component\Broadcast\Transport\TransportInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Override;

/**
 * The factory for creating broadcast hubs.
 *
 * @implements FactoryInterface<Hub>
 */
final readonly class HubFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $transport;

    /**
     * @param null|non-empty-string $transport The service identifier of the transport to use, defaults to {@see TransportInterface}.
     */
    public function __construct(null|string $transport = null)
    {
        $this->transport = $transport ?? TransportInterface::class;
    }
    #[Override]
    public function __invoke(ContainerInterface $container): object
    {
        $transport = $container->getTyped($this->transport, TransportInterface::class);

        return new Hub($transport);
    }
}
