<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\DependencyInjection\Factory\Handler\Resolver;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\Runtime\Handler\Resolver\HandlerResolver;

/**
 * Factory for creating a {@see HandlerResolver} instance.
 *
 * @implements FactoryInterface<HandlerResolver>
 */
final readonly class HandlerResolverFactory implements FactoryInterface
{
    private ?string $fallback;

    public function __construct(?string $fallback = null)
    {
        $this->fallback = $fallback;
    }

    public function __invoke(ContainerInterface $container): HandlerResolver
    {
        $fallback = null;
        if ($this->fallback !== null) {
            $fallback = $container->getTyped($this->fallback, HandlerInterface::class);
        }

        return new HandlerResolver($fallback);
    }
}
