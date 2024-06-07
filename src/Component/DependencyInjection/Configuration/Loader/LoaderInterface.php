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

namespace Neu\Component\DependencyInjection\Configuration\Loader;

use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Exception\InvalidConfigurationException;
use Neu\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @template ResourceType
 */
interface LoaderInterface
{
    /**
     * Return whether this loader is capable of loading the given resource.
     *
     * @param mixed $resource
     *
     * @psalm-assert-if-true ResourceType $resource
     */
    public function supports(mixed $resource): bool;

    /**
     * Load the given resource.
     *
     * @param ResourceType $resource
     *
     * @throws RuntimeException If failed to load the resource.
     * @throws InvalidConfigurationException If loading the resource resulted in an invalid configuration value.
     *
     * @return DocumentInterface The loaded configuration.
     */
    public function load(mixed $resource): DocumentInterface;
}
