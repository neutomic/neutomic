<?php

declare(strict_types=1);

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

    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        /** @var Configuration $configuration */
        $configuration = $container
            ->getConfiguration()
            ->getOfTypeOrDefault('advisory', $this->getConfigurationType(), [])
        ;

        $container->addDefinition(Definition::ofType(Advisory::class, new AdvisoryFactory(
            logger: $configuration['logger'] ?? null,
        )));

        $container->getDefinition(Advisory::class)->addAlias(AdvisoryInterface::class);

        $container->addDefinitions([
            Definition::ofType(Adviser\AssertationAdviser::class),
            Definition::ofType(Adviser\BlackfireAdviser::class),
            Definition::ofType(Adviser\DatadogTraceAdviser::class),
            Definition::ofType(Adviser\EventLoopDriverAdviser::class),
            Definition::ofType(Adviser\EventLoopTracingAdviser::class),
            Definition::ofType(Adviser\HPackNghttp2Adviser::class),
            Definition::ofType(Adviser\MaxExecutionTimeAdviser::class),
            Definition::ofType(Adviser\MemoryLimitAdviser::class),
            Definition::ofType(Adviser\OPCacheAdviser::class),
            Definition::ofType(Adviser\PCovAdviser::class),
            Definition::ofType(Adviser\TidewaysAdviser::class),
            Definition::ofType(Adviser\XDebugAdviser::class),
            Definition::ofType(Adviser\ZlibExtensionAdviser::class),
        ]);

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
}
