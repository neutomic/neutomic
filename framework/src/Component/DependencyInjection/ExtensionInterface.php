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

use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;

interface ExtensionInterface
{
    /**
     * Register services, processors, etc.
     *
     * @param RegistryInterface $registry The container registry.
     * @param DocumentInterface $configurations The configuration document.
     *
     * @throws ExceptionInterface If an error occurs.
     */
    public function register(RegistryInterface $registry, DocumentInterface $configurations): void;
}
