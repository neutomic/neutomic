<?php

declare(strict_types=1);

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
        /** @var Configuration $configuration */
        $configuration = $container
            ->getConfiguration()
            ->getContainer('http')
            ->getOfTypeOrDefault('session', $this->getConfigurationType(), [])
        ;

        $container->addDefinitions([
            Definition::ofType(CacheConfiguration::class, new CacheConfigurationFactory(
                $configuration['session']['cache']['expires'] ?? null,
                $configuration['session']['cache']['limiter'] ?? null,
            )),
            Definition::ofType(CookieConfiguration::class, new CookieConfigurationFactory(
                $configuration['session']['cookie']['name'] ?? null,
                $configuration['session']['cookie']['lifetime'] ?? null,
                $configuration['session']['cookie']['path'] ?? null,
                $configuration['session']['cookie']['domain'] ?? null,
                $configuration['session']['cookie']['secure'] ?? null,
                $configuration['session']['cookie']['http-only'] ?? null,
                $configuration['session']['cookie']['same-site'] ?? null,
            )),
            Definition::ofType(Initializer::class, new InitializerFactory(
                $configuration['session']['initializer']['storage'] ?? null,
                $configuration['session']['initializer']['cookie-configuration'] ?? null,
            )),
            Definition::ofType(Persistence::class, new PersistenceFactory(
                $configuration['session']['persistence']['storage'] ?? null,
                $configuration['session']['persistence']['cookie-configuration'] ?? null,
                $configuration['session']['persistence']['cache-configuration'] ?? null,
            )),
            Definition::ofType(Storage::class, new StorageFactory(
                $configuration['session']['storage']['store'] ?? null,
            ))
        ]);

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
