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

use Neu\Component\DependencyInjection\Exception\ExceptionInterface;

/**
 * A container hook which is invoked after the container is built.
 */
interface HookInterface
{
    /**
     * Invokes the hook.
     *
     * @param ContainerInterface $container The built container.
     *
     * @throws ExceptionInterface If an error occurs.
     */
    public function __invoke(ContainerInterface $container): void;
}
