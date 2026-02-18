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

namespace Neu\Component\DependencyInjection\Configuration\Resolver;

use Neu\Component\DependencyInjection\Configuration\Loader\LoaderInterface;
use Neu\Component\DependencyInjection\Configuration\Loader\ResolverAwareLoaderInterface;
use Neu\Component\DependencyInjection\Exception\NoSupportiveLoaderException;

interface ResolverInterface
{
    /**
     * Retrieve a loader capable to loading the given resource.
     *
     * If the loader implements {@see ResolverAwareLoaderInterface},
     * {@see ResolverAwareLoaderInterface::setResolver()} must be called with the current resolver.
     *
     * @template ResourceType
     *
     * @param ResourceType $resource
     *
     * @throws NoSupportiveLoaderException If no supportive loader is found.
     *
     * @return LoaderInterface<ResourceType>
     */
    public function resolve(mixed $resource): LoaderInterface;
}
