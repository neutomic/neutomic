<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection\Factory;

use Closure;
use Neu\Component\DependencyInjection\ContainerInterface;

/**
 * A factory that creates a service using a closure.
 *
 * @template T of object
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
