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

namespace Neu\Component\DependencyInjection;

use Neu\Component\Configuration\Exception\ExceptionInterface as ConfigurationExceptionInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface as DependencyInjectionExceptionInterface;

interface ExtensionInterface
{
    /**
     * Register services, processors, etc. in the container.
     *
     * @param ContainerBuilderInterface $container
     *
     * @throws DependencyInjectionExceptionInterface If an error occurs.
     * @throws ConfigurationExceptionInterface If the configuration is invalid.
     */
    public function register(ContainerBuilderInterface $container): void;
}
