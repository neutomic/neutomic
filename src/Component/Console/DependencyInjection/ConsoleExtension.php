<?php

declare(strict_types=1);

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
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Psl\Type;

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

    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        /** @var Configuration $configurations */
        $configurations = $container->getConfiguration()->getOfTypeOrDefault('console', $this->getConfigurationType(), []);

        $container->addDefinitions([
            Definition::ofType(Configuration::class, new ConfigurationFactory(
                $configurations['name'] ?? null,
                $configurations['version'] ?? null,
                $configurations['banner'] ?? null,
                $configurations['flags']['help'] ?? null,
                $configurations['flags']['quiet'] ?? null,
                $configurations['flags']['verbose'] ?? null,
                $configurations['flags']['version'] ?? null,
                $configurations['flags']['ansi'] ?? null,
                $configurations['flags']['no-ansi'] ?? null,
                $configurations['flags']['no-interaction'] ?? null,
            )),
            Definition::ofType(Registry::class, new RegistryFactory()),
            Definition::ofType(Recovery::class, new RecoveryFactory()),
            Definition::ofType(Application::class, new ApplicationFactory(
                $configurations['application']['configuration'] ?? null,
                $configurations['application']['registry'] ?? null,
                $configurations['application']['recovery'] ?? null,
            )),
        ]);

        $container->getDefinition(Registry::class)->addAlias(RegistryInterface::class);
        $container->getDefinition(Recovery::class)->addAlias(RecoveryInterface::class);
        $container->getDefinition(Application::class)->addAlias(ApplicationInterface::class);

        $container->addHook(new RegisterCommandsHook(
            $configurations['hooks']['register-commands']['registry'] ?? null,
        ));
    }
}
