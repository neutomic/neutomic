<?php

declare(strict_types=1);

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
    private string $logger;

    public function __construct(?string $logger = null)
    {
        $this->logger = $logger ?? LoggerInterface::class;
    }

    public function __invoke(ContainerInterface $container): ContentDeliverer
    {
        return new ContentDeliverer(
            $container->getTyped($this->logger, LoggerInterface::class),
        );
    }
}
