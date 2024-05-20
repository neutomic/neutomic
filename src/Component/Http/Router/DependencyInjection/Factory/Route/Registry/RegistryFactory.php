<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\DependencyInjection\Factory\Route\Registry;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Router\Route\Registry\Registry;

/**
 * @implements FactoryInterface<Registry>
 */
final readonly class RegistryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new Registry();
    }
}
