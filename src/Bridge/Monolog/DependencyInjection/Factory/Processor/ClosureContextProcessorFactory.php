<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Processor;

use Monolog\Processor\ClosureContextProcessor;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for creating the closure context processor.
 *
 * @implements FactoryInterface<ClosureContextProcessor>
 */
final readonly class ClosureContextProcessorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new ClosureContextProcessor();
    }
}
