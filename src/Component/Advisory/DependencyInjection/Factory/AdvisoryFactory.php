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

namespace Neu\Component\Advisory\DependencyInjection\Factory;

use Neu\Component\Advisory\Advisory;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see Advisory} instance.
 *
 * @implements FactoryInterface<Advisory>
 */
final readonly class AdvisoryFactory implements FactoryInterface
{
    /**
     * The logger service to use.
     *
     * @var non-empty-string
     */
    private string $logger;

    /**
     * Create a new {@see AdvisoryFactory} instance.
     *
     * @param null|non-empty-string $logger The logger service to use.
     */
    public function __construct(null|string $logger = null)
    {
        $this->logger = $logger ?? LoggerInterface::class;
    }

    public function __invoke(ContainerInterface $container): object
    {
        $logger = $container->getTyped($this->logger, LoggerInterface::class);

        return new Advisory($logger);
    }
}
