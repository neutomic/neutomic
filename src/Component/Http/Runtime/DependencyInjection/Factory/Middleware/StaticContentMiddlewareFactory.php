<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Runtime\ContentDelivery\ContentDeliverer;
use Neu\Component\Http\Runtime\Middleware\StaticContentMiddleware;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see StaticContentMiddleware} instance.
 *
 * @implements FactoryInterface<StaticContentMiddleware>
 */
final readonly class StaticContentMiddlewareFactory implements FactoryInterface
{
    private string $deliverer;
    private array $roots;
    private array $extensions;
    private string $logger;
    private int $priority;

    /**
     * @param array<string, string>|null $roots Document root directories, indexed by prefix.
     * @param array<string>|null $extensions Allowed file extensions.
     */
    public function __construct(?string $deliverer = null, ?array $roots = null, ?array $extensions = null, ?string $logger = null, ?int $priority = null)
    {
        $this->deliverer = $deliverer ?? ContentDeliverer::class;
        $this->roots = $roots ?? [];
        $this->extensions = $extensions ?? [];
        $this->logger = $logger ?? LoggerInterface::class;
        $this->priority = $priority ?? StaticContentMiddleware::PRIORITY;
    }

    public function __invoke(ContainerInterface $container): StaticContentMiddleware
    {
        return new StaticContentMiddleware(
            $container->getTyped($this->deliverer, ContentDeliverer::class),
            $this->roots,
            $this->extensions,
            $container->getTyped($this->logger, LoggerInterface::class),
            $this->priority,
        );
    }
}
