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

namespace Neu\Component\Http\Recovery\DependencyInjection;

use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\RegistryInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\Http\Recovery\DependencyInjection\Factory\RecoveryFactory;
use Neu\Component\Http\Recovery\Recovery;
use Neu\Component\Http\Recovery\RecoveryInterface;
use Psl\Type;
use Psr\Log\LogLevel;
use Throwable;
use Override;

/**
 * An extension for registering the recovery service.
 *
 * @psalm-import-type ThrowablesConfiguration from Recovery as RecoveryThrowablesConfiguration
 *
 * @psalm-type Configuration = array{
 *  logger?: non-empty-string,
 *  throwables?: RecoveryThrowablesConfiguration,
 * }
 */
final readonly class RecoveryExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function register(RegistryInterface $registry, DocumentInterface $configurations): void
    {
        $defaultLogger = $configurations->getDocument('http')->getOfTypeOrDefault('logger', Type\non_empty_string(), null);
        $configuration = $configurations->getDocument('http')->getOfTypeOrDefault('recovery', $this->getConfigurationType(), []);

        $registry->addDefinition(Definition::ofType(Recovery::class, new RecoveryFactory(
            $configuration['logger'] ??  $defaultLogger ?? null,
            $configuration['throwables'] ?? [],
        )));

        $registry->getDefinition(Recovery::class)->addAlias(RecoveryInterface::class);
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'logger' => Type\optional(Type\non_empty_string()),
            'throwables' => Type\optional(Type\dict(
                Type\class_string(Throwable::class),
                Type\shape([
                    'log_level' => Type\optional(Type\union(
                        Type\literal_scalar(LogLevel::DEBUG),
                        Type\literal_scalar(LogLevel::INFO),
                        Type\literal_scalar(LogLevel::NOTICE),
                        Type\literal_scalar(LogLevel::WARNING),
                        Type\literal_scalar(LogLevel::ERROR),
                        Type\literal_scalar(LogLevel::CRITICAL),
                        Type\literal_scalar(LogLevel::ALERT),
                        Type\literal_scalar(LogLevel::EMERGENCY),
                    )),
                    'status' => Type\optional(Type\int()),
                    'headers' => Type\optional(Type\dict(
                        Type\non_empty_string(),
                        Type\non_empty_vec(Type\non_empty_string()),
                    )),
                ])
            )),
        ]);
    }
}
