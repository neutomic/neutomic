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

namespace Neu\Component\Http\Router\DependencyInjection;

use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\Http\Router\DependencyInjection\Factory\Generator\GeneratorFactory;
use Neu\Component\Http\Router\DependencyInjection\Factory\Matcher\MatcherFactory;
use Neu\Component\Http\Router\DependencyInjection\Factory\Registry\RegistryFactory;
use Neu\Component\Http\Router\DependencyInjection\Factory\RouteCollectorFactory;
use Neu\Component\Http\Router\DependencyInjection\Factory\RouterFactory;
use Neu\Component\Http\Router\DependencyInjection\Hook\RegisterRoutesHook;
use Neu\Component\Http\Router\Generator\Generator;
use Neu\Component\Http\Router\Generator\GeneratorInterface;
use Neu\Component\Http\Router\Matcher\Matcher;
use Neu\Component\Http\Router\Matcher\MatcherInterface;
use Neu\Component\Http\Router\Registry\Registry;
use Neu\Component\Http\Router\Registry\RegistryInterface;
use Neu\Component\Http\Router\RouteCollector;
use Neu\Component\Http\Router\Router;
use Neu\Component\Http\Router\RouterInterface;
use Psl\Type;

/**
 * A dependency injection extension for the router component.
 *
 * @psalm-type Configuration = array{
 *     generator?: array{
 *         registry?: non-empty-string,
 *     },
 *     matcher?: array{
 *         registry?: non-empty-string,
 *     },
 *     router?: array{
 *         generator?: non-empty-string,
 *         matcher?: non-empty-string,
 *     },
 *     collector?: array{
 *         registry?: non-empty-string,
 *     },
 *     hooks?: array{
 *         register-routes?: array{
 *             registry?: non-empty-string,
 *         }
 *     },
 * }
 */
final readonly class RouterExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        $configuration = $container
            ->getConfiguration()
            ->getContainer('http')
            ->getOfTypeOrDefault('router', $this->getRouterConfigurationType(), []);

        // Register registry
        $definition = Definition::ofType(Registry::class, new RegistryFactory());
        $definition->addAlias(RegistryInterface::class);
        $container->addDefinition($definition);

        // Register collector
        $definition = Definition::ofType(RouteCollector::class, new RouteCollectorFactory(
            $configuration['collector']['registry'] ?? null,
        ));
        $container->addDefinition($definition);

        // Register generator
        $definition = Definition::ofType(Generator::class, new GeneratorFactory(
            $configuration['generator']['registry'] ?? null,
        ));
        $definition->addAlias(GeneratorInterface::class);
        $container->addDefinition($definition);

        // Register matcher
        $definition = Definition::ofType(Matcher::class, new MatcherFactory(
            $configuration['matcher']['registry'] ?? null,
        ));
        $definition->addAlias(MatcherInterface::class);
        $container->addDefinition($definition);

        // Register router
        $definition = Definition::ofType(Router::class, new RouterFactory(
            $configuration['router']['matcher'] ?? null,
            $configuration['router']['generator'] ?? null,
        ));
        $definition->addAlias(RouterInterface::class);
        $container->addDefinition($definition);

        $container->addHook(new RegisterRoutesHook(
            $configuration['hooks']['register-routes']['registry'] ?? null,
        ));
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getRouterConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'generator' => Type\optional(Type\shape([
                'registry' => Type\optional(Type\non_empty_string()),
            ])),
            'matcher' => Type\optional(Type\shape([
                'registry' => Type\optional(Type\non_empty_string()),
            ])),
            'router' => Type\optional(Type\shape([
                'generator' => Type\optional(Type\non_empty_string()),
                'matcher' => Type\optional(Type\non_empty_string()),
            ])),
            'collector' => Type\optional(Type\shape([
                'registry' => Type\optional(Type\non_empty_string()),
            ])),
            'hooks' => Type\optional(Type\shape([
                'register-routes' => Type\optional(Type\shape([
                    'registry' => Type\optional(Type\non_empty_string()),
                ])),
            ])),
        ]);
    }
}
