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

namespace Neu\Component\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;

/**
 * A factory is a callable that creates a service.
 *
 * @template-covariant T of object
 */
interface FactoryInterface
{
    /**
     * Create a service.
     *
     * @throws ExceptionInterface If the service cannot be created.
     *
     * @return T
     */
    public function __invoke(ContainerInterface $container): object;
}
