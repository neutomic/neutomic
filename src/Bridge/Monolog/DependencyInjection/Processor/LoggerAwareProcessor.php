<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Processor;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Definition\DefinitionInterface;
use Neu\Component\DependencyInjection\ProcessorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Processor to inject a logger into services that implement {@see LoggerAwareInterface}.
 */
final class LoggerAwareProcessor implements ProcessorInterface
{
    /**
     * The logger service identifier.
     *
     * @var string
     */
    private string $logger;

    /**
     * Create a new {@see LoggerAwareProcessor} instance.
     *
     * @param string|null $logger The logger service identifier, defaults to {@see LoggerInterface::class}.
     */
    public function __construct(?string $logger = null)
    {
        $this->logger = $logger ?? LoggerInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerInterface $container, DefinitionInterface $definition, object $service): object
    {
        if ($service instanceof LoggerAwareInterface) {
            $logger = $container->getTyped($this->logger, LoggerInterface::class);

            $service->setLogger($logger);
        }

        return $service;
    }
}
