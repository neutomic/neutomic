<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Router\Matcher\MatcherInterface;
use Neu\Component\Http\Runtime\Middleware\RouterMiddleware;

/**
 * Factory for creating a {@see RouterMiddleware} instance.
 *
 * @implements FactoryInterface<RouterMiddleware>
 */
final readonly class RouterMiddlewareFactory implements FactoryInterface
{
    private string $matcher;
    private int $priority;

    public function __construct(?string $matcher = null, ?int $priority = null)
    {
        $this->matcher = $matcher ?? MatcherInterface::class;
        $this->priority = $priority ?? RouterMiddleware::PRIORITY;
    }

    public function __invoke(ContainerInterface $container): RouterMiddleware
    {
        return new RouterMiddleware(
            $container->getTyped($this->matcher, MatcherInterface::class),
            $this->priority,
        );
    }
}
