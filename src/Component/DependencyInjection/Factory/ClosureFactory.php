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

use Closure;
use Neu\Component\DependencyInjection\ContainerInterface;

/**
 * A factory that creates a service using a closure.
 *
 * @template T of object
 *
 * @implements FactoryInterface<T>
 */
final readonly class ClosureFactory implements FactoryInterface
{
    /**
     * @param (Closure(ContainerInterface): T) $closure
     */
    public function __construct(
        private Closure $closure
    ) {
    }

    /**
     * Create a service.
     *
     * @return T
     */
    public function __invoke(ContainerInterface $container): object
    {
        return ($this->closure)($container);
    }
}
