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
use Neu\Component\Advisory\DependencyInjection\Factory\AdvisoryFactory;
use Neu\Component\Advisory\DependencyInjection\Hook\AddAdvisersHook;
use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\DependencyInjection\RegistryInterface;
use Psl\Type;

/**
 * @psalm-type Configuration = array{
 *     hooks?: array{
 *         add-advisers?: array{
 *             advisory?: non-empty-string,
 *         }
 *     },
 * }
 */
final readonly class AdvisoryExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function register(RegistryInterface $registry, DocumentInterface $configurations): void
    {
        $configuration = $configurations
            ->getOfTypeOrDefault('advisory', $this->getConfigurationType(), [])
        ;

        $registry->addDefinition(Definition::ofType(Advisory::class, new AdvisoryFactory()));

        $registry->getDefinition(Advisory::class)->addAlias(AdvisoryInterface::class);

        $registry->addDefinition(Definition::ofType(Adviser\AssertationAdviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\BlackfireAdviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\DatadogTraceAdviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\EventLoopDriverAdviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\EventLoopTracingAdviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\HPackNghttp2Adviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\MaxExecutionTimeAdviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\MemoryLimitAdviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\OPCacheAdviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\PCovAdviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\TidewaysAdviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\XDebugAdviser::class));
        $registry->addDefinition(Definition::ofType(Adviser\ZlibExtensionAdviser::class));
        $registry->addHook(new AddAdvisersHook(
            advisory: $configuration['hooks']['add-advisers']['advisory'] ?? null,
        ));
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'hooks' => Type\optional(Type\shape([
                'add-advisers' => Type\optional(Type\shape([
                    'advisory' => Type\optional(Type\non_empty_string()),
                ])),
            ])),
        ]);
    }
}
