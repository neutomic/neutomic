<?php

declare(strict_types=1);

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
     * @return ConfigurationContainerInterface<array-key> The loaded configuration.
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
