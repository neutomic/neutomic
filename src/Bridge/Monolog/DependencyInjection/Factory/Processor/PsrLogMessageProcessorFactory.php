<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Processor;

use Monolog\Processor\PsrLogMessageProcessor;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for creating the psr log message processor.
 *
 * @implements FactoryInterface<PsrLogMessageProcessor>
 */
final readonly class PsrLogMessageProcessorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new PsrLogMessageProcessor();
    }
}
