<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\DependencyInjection\Factory\Command;

use Neu\Component\Advisory\AdvisoryInterface;
use Neu\Component\Advisory\Command\AdviceCommand;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see AdviceCommand} instance.
 *
 * @implements FactoryInterface<AdviceCommand>
 */
final readonly class AdviceCommandFactory implements FactoryInterface
{
    private string $advisory;

    /**
     * @param null|string $advisory The advisory service to use.
     */
    public function __construct(?string $advisory = null)
    {
        $this->advisory = $advisory ?? AdvisoryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): AdviceCommand
    {
        return new AdviceCommand(
            $container->getTyped($this->advisory, AdvisoryInterface::class),
        );
    }
}
