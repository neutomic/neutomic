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

namespace Neu\Component\Console\DependencyInjection;

use Neu\Component\Console\Application;
use Neu\Component\Console\ApplicationInterface;
use Neu\Component\Console\Command\Registry\Registry;
use Neu\Component\Console\Command\Registry\RegistryInterface;
use Neu\Component\Console\Configuration;
use Neu\Component\Console\DependencyInjection\Factory\ApplicationFactory;
use Neu\Component\Console\DependencyInjection\Factory\Command\Registry\RegistryFactory;
use Neu\Component\Console\DependencyInjection\Factory\ConfigurationFactory;
use Neu\Component\Console\DependencyInjection\Factory\Recovery\RecoveryFactory;
use Neu\Component\Console\DependencyInjection\Hook\RegisterCommandsHook;
use Neu\Component\Console\Recovery\Recovery;
use Neu\Component\Console\Recovery\RecoveryInterface;
use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\RegistryInterface as DIRegistryInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Psl\Type;
use Override;

/**
 * @psalm-type FlagsConfiguration = array{
 *     help?: bool,
 *     quiet?: bool,
 *     verbose?: bool,
 *     version?: bool,
 *     ansi?: bool,
 *     no-ansi?: bool,
 *     no-interaction?: bool,
 * }
 * @psalm-type ApplicationConfiguration = array{
 *     configuration?: non-empty-string,
 *     registry?: non-empty-string,
 *     recovery?: non-empty-string,
 * }
 * @psalm-type HooksConfiguration = array{
 *     register-commands?: array{
 *         registry?: non-empty-string,
 *     }
 * }
 * @psalm-type Configuration = array{
 *     name?: non-empty-string,
 *     version?: non-empty-string,
 *     banner?: non-empty-string,
 *     flags?: FlagsConfiguration,
 *     application?: ApplicationConfiguration,
 *     hooks?: HooksConfiguration,
 * }
 */
final readonly class ConsoleExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function register(DIRegistryInterface $registry, DocumentInterface $configurations): void
    {
        /** @var Configuration $configuration */
        $configuration = $configurations->getOfTypeOrDefault('console', $this->getConfigurationType(), []);

        $registry->addDefinition(Definition::ofType(Configuration::class, new ConfigurationFactory(
            $configuration['name'] ?? null,
            $configuration['version'] ?? null,
            $configuration['banner'] ?? null,
            $configuration['flags']['help'] ?? null,
            $configuration['flags']['quiet'] ?? null,
            $configuration['flags']['verbose'] ?? null,
            $configuration['flags']['version'] ?? null,
            $configuration['flags']['ansi'] ?? null,
            $configuration['flags']['no-ansi'] ?? null,
            $configuration['flags']['no-interaction'] ?? null,
        )));
        $registry->addDefinition(Definition::ofType(Registry::class, new RegistryFactory()));
        $registry->addDefinition(Definition::ofType(Recovery::class, new RecoveryFactory()));
        $registry->addDefinition(Definition::ofType(Application::class, new ApplicationFactory(
            $configuration['application']['configuration'] ?? null,
            $configuration['application']['registry'] ?? null,
            $configuration['application']['recovery'] ?? null,
        )));

        $registry->getDefinition(Registry::class)->addAlias(RegistryInterface::class);
        $registry->getDefinition(Recovery::class)->addAlias(RecoveryInterface::class);
        $registry->getDefinition(Application::class)->addAlias(ApplicationInterface::class);

        $registry->addHook(new RegisterCommandsHook(
            $configuration['hooks']['register-commands']['registry'] ?? null,
        ));
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    public function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'name' => Type\optional(Type\non_empty_string()),
            'version' => Type\optional(Type\non_empty_string()),
            'banner' => Type\optional(Type\non_empty_string()),
            'flags' => Type\optional(Type\shape([
                'help' => Type\optional(Type\bool()),
                'quiet' => Type\optional(Type\bool()),
                'verbose' => Type\optional(Type\bool()),
                'version' => Type\optional(Type\bool()),
                'ansi' => Type\optional(Type\bool()),
                'no-ansi' => Type\optional(Type\bool()),
                'no-interaction' => Type\optional(Type\bool()),
            ])),
            'application' => Type\optional(Type\shape([
                'configuration' => Type\optional(Type\non_empty_string()),
                'registry' => Type\optional(Type\non_empty_string()),
                'recovery' => Type\optional(Type\non_empty_string()),
            ])),
            'hooks' => Type\optional(Type\shape([
                'register-commands' => Type\optional(Type\shape([
                    'registry' => Type\optional(Type\non_empty_string()),
                ])),
            ])),
        ]);
    }
}
