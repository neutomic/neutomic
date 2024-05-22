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

namespace Neu\Component\Http\Session\DependencyInjection\Factory\Initializer;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Session\Configuration\CookieConfiguration;
use Neu\Component\Http\Session\Initializer\Initializer;
use Neu\Component\Http\Session\Storage\StorageInterface;

/**
 * @implements FactoryInterface<Initializer>
 */
final readonly class InitializerFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $storage;

    /**
     * @var non-empty-string
     */
    private string $cookieConfiguration;

    /**
     * @param non-empty-string|null $storage
     * @param non-empty-string|null $cookieConfiguration
     */
    public function __construct(null|string $storage = null, null|string $cookieConfiguration = null)
    {
        $this->storage = $storage ?? StorageInterface::class;
        $this->cookieConfiguration = $cookieConfiguration ?? CookieConfiguration::class;
    }

    public function __invoke(ContainerInterface $container): object
    {
        return new Initializer(
            $container->getTyped($this->storage, StorageInterface::class),
            $container->getTyped($this->cookieConfiguration, CookieConfiguration::class),
        );
    }
}
