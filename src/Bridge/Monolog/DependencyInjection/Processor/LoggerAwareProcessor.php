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

namespace Neu\Bridge\Monolog\DependencyInjection\Processor;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Definition\DefinitionInterface;
use Neu\Component\DependencyInjection\ProcessorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Override;

/**
 * Processor to inject a logger into services that implement {@see LoggerAwareInterface}.
 */
final class LoggerAwareProcessor implements ProcessorInterface
{
    /**
     * The logger service identifier.
     *
     * @var non-empty-string
     */
    private string $logger;

    /**
     * Create a new {@see LoggerAwareProcessor} instance.
     *
     * @param non-empty-string|null $logger The logger service identifier, defaults to {@see LoggerInterface::class}.
     */
    public function __construct(null|string $logger = null)
    {
        $this->logger = $logger ?? LoggerInterface::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function process(ContainerInterface $container, DefinitionInterface $definition, object $service): object
    {
        if ($service instanceof LoggerAwareInterface) {
            $logger = $container->getTyped($this->logger, LoggerInterface::class);

            $service->setLogger($logger);
        }

        return $service;
    }
}
