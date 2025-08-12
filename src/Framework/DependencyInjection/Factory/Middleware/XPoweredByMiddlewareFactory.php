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

namespace Neu\Framework\DependencyInjection\Factory\Middleware;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Framework\Middleware\XPoweredByMiddleware;

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
     * Whether to expose PHP version in the powered by header.
     */
    private bool $exposePhpVersion;

    /**
     * Create a new {@see XPoweredByMiddlewareFactory} instance.
     *
     * @param non-empty-string|null $poweredBy The powered by header value.
     * @param bool|null $exposePhpVersion Whether to expose PHP version in the powered by header.
     */
    public function __construct(null|string $poweredBy = null, null|bool $exposePhpVersion = null)
    {
        $this->poweredBy = $poweredBy ?? XPoweredByMiddleware::DEFAULT_POWERED_BY;
        $this->exposePhpVersion = $exposePhpVersion ?? XPoweredByMiddleware::DEFAULT_EXPOSE_PHP_VERSION;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): XPoweredByMiddleware
    {
        return new XPoweredByMiddleware($this->poweredBy, $this->exposePhpVersion);
    }
}
