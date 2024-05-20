<?php

declare(strict_types=1);

namespace Neu\Component\Console\DependencyInjection\Factory\Command\Registry;

use Neu\Component\Console\Command\Registry\Registry;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * A factory for creating a new instance of the {@see Registry}.
 *
 * @implements FactoryInterface<Registry>
 */
final readonly class RegistryFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): Registry
    {
        return new Registry();
    }
}
