<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Runtime\Middleware\AccessLogMiddleware;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see AccessLogMiddleware} instance.
 *
 * @implements FactoryInterface<AccessLogMiddleware>
 */
final readonly class AccessLogMiddlewareFactory implements FactoryInterface
{
    private string $logger;
    private int $priority;

    public function __construct(?string $logger = null, ?int $priority = null)
    {
        $this->logger = $logger ?? LoggerInterface::class;
        $this->priority = $priority ?? AccessLogMiddleware::PRIORITY;
    }

    public function __invoke(ContainerInterface $container): AccessLogMiddleware
    {
        return new AccessLogMiddleware(
            $container->getTyped($this->logger, LoggerInterface::class),
            $this->priority,
        );
    }
}
