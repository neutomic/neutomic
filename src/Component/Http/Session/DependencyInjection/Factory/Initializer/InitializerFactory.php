<?php

declare(strict_types=1);

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
    private string $storage;
    private string $cookieConfiguration;

    public function __construct(?string $storage = null, ?string $cookieConfiguration = null)
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
