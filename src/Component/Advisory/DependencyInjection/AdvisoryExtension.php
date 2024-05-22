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

namespace Neu\Component\Advisory\DependencyInjection;

use Neu\Component\Advisory\Adviser;
use Neu\Component\Advisory\Advisory;
use Neu\Component\Advisory\AdvisoryInterface;
use Neu\Component\Advisory\Command\AdviceCommand;
use Neu\Component\Advisory\DependencyInjection\Factory\AdvisoryFactory;
use Neu\Component\Advisory\DependencyInjection\Factory\Command\AdviceCommandFactory;
use Neu\Component\Advisory\DependencyInjection\Factory\Listener\BeforeExecuteEventListenerFactory;
use Neu\Component\Advisory\DependencyInjection\Hook\AddAdvisersHook;
use Neu\Component\Advisory\Listener\BeforeExecuteEventListener;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Psl\Type;

/**
 * @psalm-type ListenersConfiguration = array{
 *     before-execute-event-listener?: array{
 *         advisory?: non-empty-string,
 *     }
 * }
 * @psalm-type CommandsConfiguration = array{
 *     advice?: array{
 *         advisory?: non-empty-string,
 *     }
 * }
 * @psalm-type HooksConfiguration = array{
 *     add-advisers?: array{
 *          advisory?: non-empty-string,
 *     }
 * }
 * @psalm-type Configuration = array{
 *     logger?: non-empty-string,
 *     listeners?: ListenersConfiguration,
 *     commands?: CommandsConfiguration,
 *     hooks?: HooksConfiguration,
 * }
 */
final readonly class AdvisoryExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        $configuration = $container
            ->getConfiguration()
            ->getOfTypeOrDefault('advisory', $this->getConfigurationType(), [])
        ;

        $container->addDefinition(Definition::ofType(Advisory::class, new AdvisoryFactory(
            logger: $configuration['logger'] ?? null,
        )));

        $container->getDefinition(Advisory::class)->addAlias(AdvisoryInterface::class);

        $container->addDefinition(Definition::ofType(Adviser\AssertationAdviser::class));
        $container->addDefinition(Definition::ofType(Adviser\BlackfireAdviser::class));
        $container->addDefinition(Definition::ofType(Adviser\DatadogTraceAdviser::class));
        $container->addDefinition(Definition::ofType(Adviser\EventLoopDriverAdviser::class));
        $container->addDefinition(Definition::ofType(Adviser\EventLoopTracingAdviser::class));
        $container->addDefinition(Definition::ofType(Adviser\HPackNghttp2Adviser::class));
        $container->addDefinition(Definition::ofType(Adviser\MaxExecutionTimeAdviser::class));
        $container->addDefinition(Definition::ofType(Adviser\MemoryLimitAdviser::class));
        $container->addDefinition(Definition::ofType(Adviser\OPCacheAdviser::class));
        $container->addDefinition(Definition::ofType(Adviser\PCovAdviser::class));
        $container->addDefinition(Definition::ofType(Adviser\TidewaysAdviser::class));
        $container->addDefinition(Definition::ofType(Adviser\XDebugAdviser::class));
        $container->addDefinition(Definition::ofType(Adviser\ZlibExtensionAdviser::class));

        $container->addDefinition(Definition::ofType(BeforeExecuteEventListener::class, new BeforeExecuteEventListenerFactory(
            advisory: $configuration['listeners']['before-execute-event-listener']['advisory'] ?? null,
        )));

        $container->addDefinition(Definition::ofType(AdviceCommand::class, new AdviceCommandFactory(
            advisory: $configuration['commands']['advice']['advisory'] ?? null,
        )));

        $container->addHook(new AddAdvisersHook(
            advisory: $configuration['hooks']['add-advisers']['advisory'] ?? null,
        ));
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'logger' => Type\optional(Type\non_empty_string()),
            'listeners' => Type\optional(Type\shape([
                'before-execute-event-listener' => Type\optional(Type\shape([
                    'advisory' => Type\optional(Type\non_empty_string()),
                ])),
            ])),
            'commands' => Type\optional(Type\shape([
                'advice' => Type\optional(Type\shape([
                    'advisory' => Type\optional(Type\non_empty_string()),
                ])),
            ])),
            'hooks' => Type\optional(Type\shape([
                'add-advisers' => Type\optional(Type\shape([
                    'advisory' => Type\optional(Type\non_empty_string()),
                ])),
            ])),
        ]);
    }
}
