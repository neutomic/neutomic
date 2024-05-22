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

namespace Neu\Bridge\Twig\DependencyInjection;

use Neu\Bridge\Twig\Cache\FilesystemCache;
use Neu\Bridge\Twig\DependencyInjection\Factory\Cache\FilesystemCacheFactory;
use Neu\Bridge\Twig\DependencyInjection\Factory\EnvironmentFactory;
use Neu\Bridge\Twig\DependencyInjection\Factory\FilesystemLoaderFactory;
use Neu\Bridge\Twig\Loader\FilesystemLoader;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Psl\Class;
use Psl\Type;
use Twig\Cache\CacheInterface;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

/**
 * @psalm-type Configuration = array{
 *      debug?: bool,
 *      charset?: non-empty-string,
 *      cache?: non-empty-string,
 *      cache-options?: int,
 *      auto-reload?: bool,
 *      strict-variables?: bool,
 *      auto-escape?: null | 'html' | 'js' | 'css' | 'name',
 *      optimizations?: int,
 *      paths?: array<non-empty-string, null | non-empty-string>,
 *      root?: non-empty-string,
 *      globals?: array<non-empty-string, mixed>,
 *  }
 */
final readonly class TwigExtension implements ExtensionInterface
{
    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'debug' => Type\optional(Type\bool()),
            'charset' => Type\optional(Type\non_empty_string()),
            'cache' => Type\optional(Type\non_empty_string()),
            'cache-options' => Type\optional(Type\int()),
            'auto-reload' => Type\optional(Type\bool()),
            'strict-variables' => Type\optional(Type\bool()),
            'auto-escape' => Type\optional(Type\union(
                Type\null(),
                Type\literal_scalar('html'),
                Type\literal_scalar('js'),
                Type\literal_scalar('css'),
                Type\literal_scalar('name'),
            )),
            'optimizations' => Type\optional(Type\int()),
            'paths' => Type\optional(Type\dict(
                Type\non_empty_string(),
                Type\union(Type\null(), Type\non_empty_string()),
            )),
            'root' => Type\optional(Type\non_empty_string()),
            'globals' => Type\optional(Type\dict(
                Type\non_empty_string(),
                Type\mixed(),
            )),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        if (!Class\exists(Environment::class)) {
            throw new RuntimeException('"' . self::class . '" extension requires the "twig/twig" package to be installed, please run "composer require twig/twig:^4.0".');
        }

        $configuration = $container->getConfiguration()->getOfTypeOrDefault('twig', $this->getConfigurationType(), []);

        $cache = $configuration['cache'] ?? null;
        if (null !== $cache) {
            $container->addDefinition(Definition::ofType(FilesystemCache::class, new FilesystemCacheFactory(
                cache: $cache,
                options: $configuration['cache-options'] ?? null,
            )));

            $container->getDefinition(FilesystemCache::class)->addAlias(CacheInterface::class);
        }

        $container->addDefinition(Definition::ofType(FilesystemLoader::class, new FilesystemLoaderFactory(
            $configuration['paths'] ?? null,
            $configuration['root'] ?? null,
        )));

        $container->addDefinition(Definition::ofType(Environment::class, new EnvironmentFactory(
            $configuration['debug'] ?? null,
            $configuration['charset'] ?? null,
            $configuration['auto-reload'] ?? null,
            $configuration['strict-variables'] ?? null,
            $configuration['auto-escape'] ?? null,
            $configuration['optimizations'] ?? null,
            $configuration['globals'] ?? null,
        )));

        $container->getDefinition(FilesystemLoader::class)->addAlias(LoaderInterface::class);
    }
}
