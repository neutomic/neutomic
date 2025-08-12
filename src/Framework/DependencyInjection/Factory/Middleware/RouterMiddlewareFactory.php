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

namespace Neu\Framework\DependencyInjection\Factory\Middleware;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Router\Matcher\MatcherInterface;
use Neu\Framework\Middleware\RouterMiddleware;
use Override;

/**
 * Factory for creating a {@see RouterMiddleware} instance.
 *
 * @implements FactoryInterface<RouterMiddleware>
 */
final readonly class RouterMiddlewareFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $matcher;

    /**
     * @var int
     */
    private int $priority;

    /**
     * @param non-empty-string|null $matcher Router matcher service identifier.
     * @param int|null $priority Middleware priority.
     */
    public function __construct(null|string $matcher = null, null|int $priority = null)
    {
        $this->matcher = $matcher ?? MatcherInterface::class;
        $this->priority = $priority ?? RouterMiddleware::PRIORITY;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(ContainerInterface $container): RouterMiddleware
    {
        return new RouterMiddleware(
            $container->getTyped($this->matcher, MatcherInterface::class),
            $this->priority,
        );
    }
}
