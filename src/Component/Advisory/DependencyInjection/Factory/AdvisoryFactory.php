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

namespace Neu\Component\Advisory\DependencyInjection\Factory;

use Neu\Component\Advisory\Advisory;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see Advisory} instance.
 *
 * @implements FactoryInterface<Advisory>
 */
final readonly class AdvisoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new Advisory();
    }
}
