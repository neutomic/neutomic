<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;

/**
 * A factory is a callable that creates a service.
 *
 * @template T of object
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
