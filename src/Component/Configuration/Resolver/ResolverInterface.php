<?php

declare(strict_types=1);

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
