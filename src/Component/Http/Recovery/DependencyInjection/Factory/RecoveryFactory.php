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

namespace Neu\Component\Http\Recovery\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Recovery\Recovery;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see Recovery} instance.
 *
 * @psalm-import-type ThrowablesConfiguration from Recovery
 *
 * @implements FactoryInterface<Recovery>
 */
final readonly class RecoveryFactory implements FactoryInterface
{
    /**
     * The logger service identifier.
     *
     * @var non-empty-string
     */
    private string $logger;

    /**
     * @var ThrowablesConfiguration
     */
    private array $throwables;

    /**
     * @param non-empty-string|null $logger The logger service identifier.
     * @param ThrowablesConfiguration $throwables The throwables configuration.
     */
    public function __construct(null|string $logger = null, array $throwables = [])
    {
        $this->logger = $logger ?? LoggerInterface::class;
        $this->throwables = $throwables;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): Recovery
    {
        $logger = $container->getTyped($this->logger, LoggerInterface::class);

        return new Recovery($container->getProject()->debug, $logger, $this->throwables);
    }
}
