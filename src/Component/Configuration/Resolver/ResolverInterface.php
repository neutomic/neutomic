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

namespace Neu\Component\Configuration\Resolver;

use Neu\Component\Configuration\Exception\NoSupportiveLoaderException;
use Neu\Component\Configuration\Loader\LoaderInterface;
use Neu\Component\Configuration\Loader\ResolverAwareLoaderInterface;

interface ResolverInterface
{
    /**
     * Retrieve a loader capable to loading the given resource.
     *
     * If the loader implements {@see ResolverAwareLoaderInterface},
     * {@see ResolverAwareLoaderInterface::setResolver()} must be called with the current resolver.
     *
     * @template T
     *
     * @param T $resource
     *
     * @throws NoSupportiveLoaderException If no supportive loader is found.
     *
     * @return LoaderInterface<T>
     */
    public function resolve(mixed $resource): LoaderInterface;
}
