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

namespace Neu\Component\Configuration\Loader;

use Neu\Component\Configuration\ConfigurationContainerInterface;
use Neu\Component\Configuration\Exception;

/**
 * @template T
 */
interface LoaderInterface
{
    /**
     * Load the given resource.
     *
     * @param T $resource
     *
     * @throws Exception\InvalidConfigurationException If loading the resource resulted in an invalid configuration value.
     *
     * @return ConfigurationContainerInterface The loaded configuration.
     */
    public function load(mixed $resource): ConfigurationContainerInterface;

    /**
     * Return whether this loader is capable of loading the given resource.
     *
     * @param mixed $resource
     *
     * @psalm-assert-if-true T $resource
     */
    public function supports(mixed $resource): bool;
}
