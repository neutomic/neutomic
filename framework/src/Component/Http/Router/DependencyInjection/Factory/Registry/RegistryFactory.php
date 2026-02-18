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

namespace Neu\Component\Http\Router\DependencyInjection\Factory\Registry;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Router\Registry\Registry;
use Override;

/**
 * @implements FactoryInterface<Registry>
 */
final readonly class RegistryFactory implements FactoryInterface
{
    #[Override]
    public function __invoke(ContainerInterface $container): object
    {
        return new Registry();
    }
}
