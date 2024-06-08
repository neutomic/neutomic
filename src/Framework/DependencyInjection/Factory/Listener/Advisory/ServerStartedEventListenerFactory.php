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

namespace Neu\Framework\DependencyInjection\Factory\Listener\Advisory;

use Neu\Component\Advisory\AdvisoryInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Framework\Listener\Advisory\ServerStartedEventListener;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see ServerStartedEventListener} instance.
 *
 * @implements FactoryInterface<ServerStartedEventListener>
 */
final readonly class ServerStartedEventListenerFactory implements FactoryInterface
{
    /**
     * The advisory service to use.
     *
     * @var non-empty-string
     */
    private string $advisory;

    /**
     * The logger service to use.
     *
     * @var non-empty-string
     */
    private string $logger;

    /**
     * @param null|non-empty-string $advisory The advisory service to use.
     */
    public function __construct(null|string $advisory = null, null|string $logger = null)
    {
        $this->advisory = $advisory ?? AdvisoryInterface::class;
        $this->logger = $logger ?? LoggerInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): ServerStartedEventListener
    {
        return new ServerStartedEventListener(
            $container->getProject()->mode,
            $container->getTyped($this->advisory, AdvisoryInterface::class),
            $container->getTyped($this->logger, LoggerInterface::class),
        );
    }
}
