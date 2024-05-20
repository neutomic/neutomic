<?php

declare(strict_types=1);

namespace Neu\Component\Console\DependencyInjection\Factory\Recovery;

use Neu\Component\Console\Recovery\Recovery;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * A factory for creating a new instance of the {@see Recovery}.
 *
 * @implements FactoryInterface<Recovery>
 */
final readonly class RecoveryFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): Recovery
    {
        return new Recovery();
    }
}
