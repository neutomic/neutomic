<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Processor;

use Monolog\Processor\HostnameProcessor;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for creating the hostname processor.
 *
 * @implements FactoryInterface<HostnameProcessor>
 */
final readonly class HostnameProcessorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new HostnameProcessor();
    }
}
