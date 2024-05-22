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
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class StaticContentMiddlewareFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $deliverer;

    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private array $roots;

    /**
     * @var list<non-empty-string>
     */
    private array $extensions;

    /**
     * @var non-empty-string
     */
    private string $logger;

    /**
     * @var int
     */
    private int $priority;

    /**
     * @param non-empty-string|null $deliverer Content deliverer service identifier.
     * @param array<non-empty-string, non-empty-string>|null $roots Document root directories, indexed by prefix.
     * @param list<non-empty-string>|null $extensions Allowed file extensions.
     * @param non-empty-string|null $logger Logger service identifier.
     * @param int|null $priority Middleware priority.
     */
    public function __construct(null|string $deliverer = null, null|array $roots = null, null|array $extensions = null, null|string $logger = null, null|int $priority = null)
    {
        $this->deliverer = $deliverer ?? ContentDeliverer::class;
        $this->roots = $roots ?? [];
        $this->extensions = $extensions ?? [];
        $this->logger = $logger ?? LoggerInterface::class;
        $this->priority = $priority ?? StaticContentMiddleware::PRIORITY;
    }

    /**
     * @inheritDoc
     */
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
