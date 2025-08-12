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
use Neu\Framework\Middleware\AccessLogMiddleware;
use Psr\Log\LoggerInterface;
use Override;

/**
 * Factory for creating a {@see AccessLogMiddleware} instance.
 *
 * @implements FactoryInterface<AccessLogMiddleware>
 */
final readonly class AccessLogMiddlewareFactory implements FactoryInterface
{
    /**
     * The logger used to log events.
     *
     * @var non-empty-string
     */
    private string $logger;

    /**
     * The priority of the middleware.
     *
     * @var int
     */
    private int $priority;

    /**
     * @param non-empty-string|null $logger Logger service identifier.
     * @param int|null $priority Middleware priority.
     */
    public function __construct(null|string $logger = null, null|int $priority = null)
    {
        $this->logger = $logger ?? LoggerInterface::class;
        $this->priority = $priority ?? AccessLogMiddleware::PRIORITY;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(ContainerInterface $container): AccessLogMiddleware
    {
        return new AccessLogMiddleware(
            $container->getTyped($this->logger, LoggerInterface::class),
            $this->priority,
        );
    }
}
