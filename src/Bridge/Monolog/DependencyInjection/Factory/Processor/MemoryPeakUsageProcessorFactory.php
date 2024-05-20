<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Processor;

use Monolog\Processor\MemoryPeakUsageProcessor;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for creating the memory peak usage processor.
 *
 * @implements FactoryInterface<MemoryPeakUsageProcessor>
 */
final readonly class MemoryPeakUsageProcessorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new MemoryPeakUsageProcessor();
    }
}
