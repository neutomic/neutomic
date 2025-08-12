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

namespace Neu\Component\Http\Runtime\DependencyInjection\Factory\ContentDelivery;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Runtime\ContentDelivery\ContentDeliverer;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see ContentDeliverer} instance.
 *
 * @implements FactoryInterface<ContentDeliverer>
 */
final readonly class ContentDelivererFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $logger;

    /**
     * @param non-empty-string|null $logger
     */
    public function __construct(null|string $logger = null)
    {
        $this->logger = $logger ?? LoggerInterface::class;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container): ContentDeliverer
    {
        return new ContentDeliverer(
            $container->getTyped($this->logger, LoggerInterface::class),
        );
    }
}
