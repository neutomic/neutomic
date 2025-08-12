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

namespace Neu\Component\Console\DependencyInjection\Factory\Recovery;

use Neu\Component\Console\Recovery\Recovery;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Override;

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
    #[Override]
    public function __invoke(ContainerInterface $container): Recovery
    {
        return new Recovery();
    }
}
