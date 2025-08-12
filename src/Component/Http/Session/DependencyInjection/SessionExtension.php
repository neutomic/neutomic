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

use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\DependencyInjection\RegistryInterface;
use Neu\Component\Http\Message\CookieSameSite;
use Neu\Component\Http\Session\Configuration\CacheConfiguration;
use Neu\Component\Http\Session\Configuration\CacheLimiter;
use Neu\Component\Http\Session\Configuration\CookieConfiguration;
use Neu\Component\Http\Session\DependencyInjection\Factory\Configuration\CacheConfigurationFactory;
use Neu\Component\Http\Session\DependencyInjection\Factory\Configuration\CookieConfigurationFactory;
use Neu\Component\Http\Session\DependencyInjection\Factory\Handler\EncryptedHandlerFactory;
use Neu\Component\Http\Session\DependencyInjection\Factory\Persistence\PersistenceFactory;
use Neu\Component\Http\Session\DependencyInjection\Factory\Handler\CacheHandlerFactory;
use Neu\Component\Http\Session\Handler\CacheHandler;
use Neu\Component\Http\Session\Handler\EncryptedHandler;
use Neu\Component\Http\Session\Persistence\PersistenceInterface;
use Neu\Component\Http\Session\Handler\HandlerInterface;
use Psl\Type;

/**
 * A container extension for the session component.
 *
 * @psalm-type CacheHandlerConfiguration = array{
 *     type: 'cache',
 *     store?: non-empty-string,
 * }
 * @psalm-type EncryptedHandlerConfiguration = array{
 *     type: 'encrypted',
 *     secret?: non-empty-string,
 * }
 * @psalm-type Configuration = array{
 *     cookie?: array{
 *         name?: non-empty-string,
 *         lifetime?: int,
 *         path?: non-empty-string,
 *         domain?: non-empty-string,
 *         secure?: bool,
 *         http-only?: bool,
 *         same-site?: CookieSameSite,
 *     },
 *     cache?: array{
 *         expires?: int,
 *         limiter?: CacheLimiter,
 *     },
 *     handler?: CacheHandlerConfiguration|EncryptedHandlerConfiguration,
 *     persistence?: array{
 *         handler?: non-empty-string,
 *         cookie-configuration?: non-empty-string,
 *         cache-configuration?: non-empty-string,
 *     }
 * }
 */
final readonly class SessionExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function register(RegistryInterface $registry, DocumentInterface $configurations): void
    {
        $configuration = $configurations->getDocument('http')->getOfTypeOrDefault('session', $this->getConfigurationType(), []);

        $registry->addDefinition(Definition::ofType(CacheConfiguration::class, new CacheConfigurationFactory(
            $configuration['cache']['expires'] ?? null,
            $configuration['cache']['limiter'] ?? null,
        )));

        $registry->addDefinition(Definition::ofType(CookieConfiguration::class, new CookieConfigurationFactory(
            $configuration['cookie']['name'] ?? null,
            $configuration['cookie']['lifetime'] ?? null,
            $configuration['cookie']['path'] ?? null,
            $configuration['cookie']['domain'] ?? null,
            $configuration['cookie']['secure'] ?? null,
            $configuration['cookie']['http-only'] ?? null,
            $configuration['cookie']['same-site'] ?? null,
        )));

        $handler = $configuration['handler'] ?? ['type' => 'encrypted', 'secret' => null];
        if ('cache' === $handler['type']) {
            /** @var CacheHandlerConfiguration $handler */
            $this->registerCacheHandler($registry, $handler);
        } else {
            /** @var EncryptedHandlerConfiguration $handler */
            $this->registerEncryptedHandler($registry, $handler);
        }

        $registry->addDefinition(Definition::ofType(PersistenceInterface::class, new PersistenceFactory(
            $configuration['persistence']['handler'] ?? null,
            $configuration['persistence']['cookie-configuration'] ?? null,
            $configuration['persistence']['cache-configuration'] ?? null,
        )));
    }

    /**
     * Register the cache handler.
     *
     * @param CacheHandlerConfiguration $configuration
     *
     * @throws ExceptionInterface
     */
    private function registerCacheHandler(RegistryInterface $registry, array $configuration): void
    {
        $registry->addDefinition(Definition::ofType(CacheHandler::class, new CacheHandlerFactory(
            $configuration['store'] ?? null,
        )));
        $registry->getDefinition(CacheHandler::class)->addAlias(HandlerInterface::class);
    }

    /**
     * Register the encrypted handler.
     *
     * @param EncryptedHandlerConfiguration $configuration
     *
     * @throws ExceptionInterface
     */
    private function registerEncryptedHandler(RegistryInterface $registry, array $configuration): void
    {
        $registry->addDefinition(Definition::ofType(EncryptedHandler::class, new EncryptedHandlerFactory(
            $configuration['secret'] ?? null,
        )));
        $registry->getDefinition(EncryptedHandler::class)->addAlias(HandlerInterface::class);
    }

    /**
     * @psalm-return Type\TypeInterface<Configuration>
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
                        static fn(string $value): CacheLimiter => CacheLimiter::from($value),
                    )
                ),
            ])),
            'handler' => Type\optional(Type\union(
                Type\shape([
                    'type' => Type\literal_scalar('cache'),
                    'store' => Type\optional(Type\non_empty_string()),
                ]),
                Type\shape([
                    'type' => Type\literal_scalar('encrypted'),
                    'secret' => Type\optional(Type\non_empty_string()),
                ]),
            )),
            'persistence' => Type\optional(Type\shape([
                'handler' => Type\optional(Type\non_empty_string()),
                'cookie-configuration' => Type\optional(Type\non_empty_string()),
                'cache-configuration' => Type\optional(Type\non_empty_string()),
            ])),
        ]);
    }
}
