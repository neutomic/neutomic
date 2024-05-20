<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Processor;

use Monolog\Processor\ProcessIdProcessor;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for creating the process id processor.
 *
 * @implements FactoryInterface<ProcessIdProcessor>
 */
final readonly class ProcessIdProcessorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new ProcessIdProcessor();
    }
}
