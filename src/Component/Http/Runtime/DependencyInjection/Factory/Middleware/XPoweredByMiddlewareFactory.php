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

namespace Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Runtime\Middleware\XPoweredByMiddleware;

/**
 * Factory for creating a {@see XPoweredByMiddleware} instance.
 *
 * @implements FactoryInterface<XPoweredByMiddleware>
 */
final readonly class XPoweredByMiddlewareFactory implements FactoryInterface
{
    /**
     * The powered by header value.
     *
     * @var non-empty-string
     */
    private string $poweredBy;

    /**
     * @param non-empty-string|null $poweredBy The powered by header value.
     */
    public function __construct(null|string $poweredBy = null)
    {
        $this->poweredBy = $poweredBy ?? XPoweredByMiddleware::DEFAULT_POWERED_BY;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): XPoweredByMiddleware
    {
        return new XPoweredByMiddleware($this->poweredBy);
    }
}
