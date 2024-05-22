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

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Processor;

use Monolog\Processor\MemoryUsageProcessor;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for creating the memory usage processor.
 *
 * @implements FactoryInterface<MemoryUsageProcessor>
 */
final readonly class MemoryUsageProcessorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new MemoryUsageProcessor();
    }
}
