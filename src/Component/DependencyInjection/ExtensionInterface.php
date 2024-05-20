<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection;

interface ExtensionInterface
{
    /**
     * Register services, processors, etc. in the container.
     *
     * @param ContainerBuilderInterface $container
     */
    public function register(ContainerBuilderInterface $container): void;
}
