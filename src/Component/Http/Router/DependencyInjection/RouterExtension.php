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

use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\DependencyInjection\RegistryInterface as DIRegistryInterface;
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
use Override;

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
    #[Override]
    public function register(DIRegistryInterface $registry, DocumentInterface $configurations): void
    {
        $configuration = $configurations->getDocument('http')->getOfTypeOrDefault('router', $this->getRouterConfigurationType(), []);

        // Register registry
        $registry->addDefinition(Definition::ofType(Registry::class, new RegistryFactory()));
        $registry->addDefinition(Definition::ofType(RouteCollector::class, new RouteCollectorFactory(
            $configuration['collector']['registry'] ?? null,
        )));
        $registry->addDefinition(Definition::ofType(Generator::class, new GeneratorFactory(
            $configuration['generator']['registry'] ?? null,
        )));
        $registry->addDefinition(Definition::ofType(Matcher::class, new MatcherFactory(
            $configuration['matcher']['registry'] ?? null,
        )));
        $registry->addDefinition(Definition::ofType(Router::class, new RouterFactory(
            $configuration['router']['matcher'] ?? null,
            $configuration['router']['generator'] ?? null,
        )));

        $registry->getDefinition(Registry::class)->addAlias(RegistryInterface::class);
        $registry->getDefinition(Generator::class)->addAlias(GeneratorInterface::class);
        $registry->getDefinition(Matcher::class)->addAlias(MatcherInterface::class);
        $registry->getDefinition(Router::class)->addAlias(RouterInterface::class);

        $registry->addHook(new RegisterRoutesHook(
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
