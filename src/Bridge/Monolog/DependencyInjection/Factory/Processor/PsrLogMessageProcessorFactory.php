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
    #[\Override]
    public function __invoke(ContainerInterface $container): object
    {
        return new PsrLogMessageProcessor();
    }
}
