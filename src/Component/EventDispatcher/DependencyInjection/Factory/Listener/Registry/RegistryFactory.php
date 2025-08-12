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

namespace Neu\Component\EventDispatcher\DependencyInjection\Factory\Listener\Registry;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\EventDispatcher\Listener\Registry\Registry;

/**
 * A factory for creating a new instance of the {@see Registry}.
 *
 * @implements FactoryInterface<Registry>
 */
final readonly class RegistryFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): Registry
    {
        return new Registry();
    }
}
