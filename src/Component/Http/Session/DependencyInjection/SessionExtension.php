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

namespace Neu\Component\Http\Session\DependencyInjection;

use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\Http\Message\CookieSameSite;
use Neu\Component\Http\Session\Configuration\CacheConfiguration;
use Neu\Component\Http\Session\Configuration\CacheLimiter;
use Neu\Component\Http\Session\Configuration\CookieConfiguration;
use Neu\Component\Http\Session\DependencyInjection\Factory\Configuration\CacheConfigurationFactory;
use Neu\Component\Http\Session\DependencyInjection\Factory\Configuration\CookieConfigurationFactory;
use Neu\Component\Http\Session\DependencyInjection\Factory\Initializer\InitializerFactory;
use Neu\Component\Http\Session\DependencyInjection\Factory\Persistence\PersistenceFactory;
use Neu\Component\Http\Session\DependencyInjection\Factory\Storage\StorageFactory;
use Neu\Component\Http\Session\Initializer\Initializer;
use Neu\Component\Http\Session\Initializer\InitializerInterface;
use Neu\Component\Http\Session\Persistence\Persistence;
use Neu\Component\Http\Session\Persistence\PersistenceInterface;
use Neu\Component\Http\Session\Storage\Storage;
use Neu\Component\Http\Session\Storage\StorageInterface;
use Psl\Type;

/**
 * A container extension for the session component.
 *
 * @psalm-type Configuration = array{
 *     cookie?: array{
 *         name?: non-empty-string,
 *         lifetime?: int,
 *         path?: non-empty-string,
 *         domain?: non-empty-string,
 *         secure?: bool,
 *         http-only?: bool,
 *         same-site?: CookieSameSite|string,
 *     },
 *     cache?: array{
 *         expires?: int,
 *         limiter?: CacheLimiter|string,
 *     },
 *     storage?: array{
 *         store?: non-empty-string,
 *     },
 *     initializer?: array{
 *         storage?: non-empty-string,
 *         cookie-configuration?: non-empty-string,
 *     },
 *     persistence?: array{
 *         storage?: non-empty-string,
 *         cookie-configuration?: non-empty-string,
 *         cache-configuration?: non-empty-string,
 *     }
 * }
 */
final readonly class SessionExtension implements ExtensionInterface
{
    public function register(ContainerBuilderInterface $container): void
    {
        $configuration = $container
            ->getConfiguration()
            ->getContainer('http')
            ->getOfTypeOrDefault('session', $this->getConfigurationType(), [])
        ;

        $container->addDefinition(Definition::ofType(CacheConfiguration::class, new CacheConfigurationFactory(
            $configuration['cache']['expires'] ?? null,
            $configuration['cache']['limiter'] ?? null,
        )));

        $container->addDefinition(Definition::ofType(CookieConfiguration::class, new CookieConfigurationFactory(
            $configuration['cookie']['name'] ?? null,
            $configuration['cookie']['lifetime'] ?? null,
            $configuration['cookie']['path'] ?? null,
            $configuration['cookie']['domain'] ?? null,
            $configuration['cookie']['secure'] ?? null,
            $configuration['cookie']['http-only'] ?? null,
            $configuration['cookie']['same-site'] ?? null,
        )));

        $container->addDefinition(Definition::ofType(Initializer::class, new InitializerFactory(
            $configuration['initializer']['storage'] ?? null,
            $configuration['initializer']['cookie-configuration'] ?? null,
        )));

        $container->addDefinition(Definition::ofType(Persistence::class, new PersistenceFactory(
            $configuration['persistence']['storage'] ?? null,
            $configuration['persistence']['cookie-configuration'] ?? null,
            $configuration['persistence']['cache-configuration'] ?? null,
        )));

        $container->addDefinition(Definition::ofType(Storage::class, new StorageFactory(
            $configuration['storage']['store'] ?? null,
        )));

        $container->getDefinition(Initializer::class)->addAlias(InitializerInterface::class);
        $container->getDefinition(Persistence::class)->addAlias(PersistenceInterface::class);
        $container->getDefinition(Storage::class)->addAlias(StorageInterface::class);
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'cookie' => Type\optional(Type\shape([
                'name' => Type\optional(Type\non_empty_string()),
                'lifetime' => Type\optional(Type\int()),
                'path' => Type\optional(Type\non_empty_string()),
                'domain' => Type\optional(Type\non_empty_string()),
                'secure' => Type\optional(Type\bool()),
                'http-only' => Type\optional(Type\bool()),
                'same-site' => Type\optional(Type\backed_enum(CookieSameSite::class)),
            ])),
            'cache' => Type\optional(Type\shape([
                'expires' => Type\optional(Type\int()),
                'limiter' => Type\optional(
                    Type\converted(
                        Type\union(
                            Type\literal_scalar('nocache'),
                            Type\literal_scalar('public'),
                            Type\literal_scalar('private'),
                            Type\literal_scalar('private-no-expire'),
                        ),
                        Type\backed_enum(CacheLimiter::class),
                        CacheLimiter::from(...),
                    )
                ),
            ])),
            'storage' => Type\optional(Type\shape([
                'store' => Type\optional(Type\non_empty_string()),
            ])),
            'initializer' => Type\optional(Type\shape([
                'storage' => Type\optional(Type\non_empty_string()),
                'cookie-configuration' => Type\optional(Type\non_empty_string()),
            ])),
            'persistence' => Type\optional(Type\shape([
                'storage' => Type\optional(Type\non_empty_string()),
                'cookie-configuration' => Type\optional(Type\non_empty_string()),
                'cache-configuration' => Type\optional(Type\non_empty_string()),
            ])),
        ]);
    }
}
