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

namespace Neu\Component\Http\Session\DependencyInjection\Factory\Persistence;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Session\Configuration\CacheConfiguration;
use Neu\Component\Http\Session\Configuration\CookieConfiguration;
use Neu\Component\Http\Session\Persistence\Persistence;
use Neu\Component\Http\Session\Handler\HandlerInterface;
use Override;

/**
 * Factory for creating {@see Persistence} instances.
 *
 * @implements FactoryInterface<Persistence>
 */
final readonly class PersistenceFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $handler;

    /**
     * @var non-empty-string
     */
    private string $cookieConfiguration;

    /**
     * @var non-empty-string
     */
    private string $cacheConfiguration;

    /**
     * @param non-empty-string|null $handler
     * @param non-empty-string|null $cookieConfiguration
     * @param non-empty-string|null $cacheConfiguration
     */
    public function __construct(null|string $handler = null, null|string $cookieConfiguration = null, null|string $cacheConfiguration = null)
    {
        $this->handler = $handler ?? HandlerInterface::class;
        $this->cookieConfiguration = $cookieConfiguration ?? CookieConfiguration::class;
        $this->cacheConfiguration = $cacheConfiguration ?? CacheConfiguration::class;
    }

    #[Override]
    public function __invoke(ContainerInterface $container): Persistence
    {
        return new Persistence(
            $container->getTyped($this->handler, HandlerInterface::class),
            $container->getTyped($this->cookieConfiguration, CookieConfiguration::class),
            $container->getTyped($this->cacheConfiguration, CacheConfiguration::class),
        );
    }
}
