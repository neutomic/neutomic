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
    /**
     * @var non-empty-string|null
     */
    private null|string $fallback;

    /**
     * @param non-empty-string|null $fallback Fallback handler service identifier.
     */
    public function __construct(null|string $fallback = null)
    {
        $this->fallback = $fallback;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): HandlerResolver
    {
        $fallback = null;
        if ($this->fallback !== null) {
            $fallback = $container->getTyped($this->fallback, HandlerInterface::class);
        }

        return new HandlerResolver($fallback);
    }
}
