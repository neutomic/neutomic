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

namespace Neu\Component\Password\DependencyInjection;

use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Exception\InvalidConfigurationException;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\DependencyInjection\RegistryInterface;
use Neu\Component\Password\HasherInterface;
use Neu\Component\Password\HasherManagerInterface;
use Neu\Component\Password\HasherManager;
use Neu\Component\Password\NativeHasher;
use Neu\Component\Password\PlainHasher;
use Psl\Type;
use Override;

/**
 * The password extension.
 *
 * @psalm-type PlainHasherConfiguration = array{
 *     type: 'plain',
 * }
 * @psalm-type NativeHasherConfiguration = array{
 *     type: 'native',
 *     algorithm?: 'default'|'bcrypt'|'argon2i'|'argon2id',
 *     options?: array{
 *         cost?: int,
 *         time_cost?: int,
 *         memory_cost?: int,
 *         threads?: int,
 *     }
 * }
 * @psalm-type HasherConfiguration = PlainHasherConfiguration|NativeHasherConfiguration
 * @psalm-type Configuration = array{
 *     default?: non-empty-string,
 *     hashers?: array<non-empty-string, HasherConfiguration>
 * }
 */
final readonly class PasswordExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function register(RegistryInterface $registry, DocumentInterface $configurations): void
    {
        $configuration = $configurations
            ->getOfTypeOrDefault('password', $this->getConfigurationType(), [])
        ;

        $hashers = $configuration['hashers'] ?? [];

        // If no hashers are defined, default to a single native hasher.
        if ([] === $hashers) {
            $hashers = ['default' => ['type' => 'native']];
        }

        $hasherDefinitions = $this->registerHashers($registry, $hashers);

        $defaultHasher = $configuration['default'] ?? array_key_first($hasherDefinitions);

        $this->setDefaultHasher($registry, $hasherDefinitions, $defaultHasher);

        $this->registerHasherManager($registry, $defaultHasher, $hasherDefinitions);
    }

    /**
     * Register password hashers.
     *
     * @param RegistryInterface $registry
     * @param non-empty-array<non-empty-string, HasherConfiguration> $hashers
     *
     * @return non-empty-array<non-empty-string, non-empty-string> Map of password hasher names to password hasher service IDs
     */
    private function registerHashers(RegistryInterface $registry, array $hashers): array
    {
        $hasherDefinitions = [];

        foreach ($hashers as $name => $config) {
            $hasherServiceId = 'password.' . $name;

            $type = $config['type'];
            if ('plain' === $type) {
                $this->registerPlainHasher($registry, $hasherServiceId);
            } else {
                /** @var NativeHasherConfiguration $config */
                $this->registerNativeHasher($registry, $hasherServiceId, $config);
            }

            $hasherDefinitions[$name] = $hasherServiceId;
        }

        return $hasherDefinitions;
    }

    /**
     * Register a plain password hasher.
     *
     * @param RegistryInterface $registry
     * @param non-empty-string $serviceId
     */
    private function registerPlainHasher(RegistryInterface $registry, string $serviceId): void
    {
        $registry->addDefinition(Definition::create($serviceId, PlainHasher::class, new Factory\PlainHasherFactory()));
    }

    /**
     * Register a native password hasher.
     *
     * @param RegistryInterface $registry
     * @param non-empty-string $serviceId
     * @param NativeHasherConfiguration $config
     */
    private function registerNativeHasher(RegistryInterface $registry, string $serviceId, array $config): void
    {
        $registry->addDefinition(Definition::create($serviceId, NativeHasher::class, new Factory\NativeHasherFactory(
            $config['algorithm'] ?? null,
            $config['options'] ?? null,
        )));
    }

    /**
     * Set the default password hasher.
     *
     * @param RegistryInterface $registry
     * @param array<non-empty-string, non-empty-string> $hasherDefinitions
     * @param non-empty-string $defaultHasherId
     *
     * @throws ExceptionInterface
     */
    private function setDefaultHasher(RegistryInterface $registry, array $hasherDefinitions, string $defaultHasherId): void
    {
        if (!isset($hasherDefinitions[$defaultHasherId])) {
            if (!$registry->hasDefinition($defaultHasherId)) {
                throw new InvalidConfigurationException(
                    'The default password hasher "' . $defaultHasherId . '" is not defined.',
                );
            }

            $definition = $registry->getDefinition($defaultHasherId);
        } else {
            $definition = $registry->getDefinition($hasherDefinitions[$defaultHasherId]);
        }

        if (!$definition->isInstanceOf(HasherInterface::class)) {
            throw new InvalidConfigurationException(
                'The default password hasher "' . $defaultHasherId . '" must be an instance of "' . HasherInterface::class . '".',
            );
        }

        $definition->addAlias(HasherInterface::class);
    }


    /**
     * Register the {@see HasherManager} service.
     *
     * @param RegistryInterface $registry
     * @param non-empty-string $defaultHasherId
     * @param array<non-empty-string, non-empty-string> $hasherDefinitions
     */
    private function registerHasherManager(RegistryInterface $registry, string $defaultHasherId, array $hasherDefinitions): void
    {
        $definition = Definition::ofType(HasherManager::class, new Factory\HasherManagerFactory($defaultHasherId, $hasherDefinitions));
        $definition->addAlias(HasherManagerInterface::class);

        $registry->addDefinition($definition);
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    public function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'default' => Type\optional(Type\non_empty_string()),
            'hashers' => Type\optional(Type\dict(
                Type\non_empty_string(),
                Type\union(
                    Type\shape([
                        'type' => Type\literal_scalar('plain')
                    ]),
                    Type\shape([
                        'type' => Type\literal_scalar('native'),
                        'algorithm' => Type\optional(Type\union(
                            Type\literal_scalar('default'),
                            Type\literal_scalar('bcrypt'),
                            Type\literal_scalar('argon2i'),
                            Type\literal_scalar('argon2id')
                        )),
                        'options' => Type\optional(Type\shape([
                            'cost' => Type\optional(Type\int()),
                            'time_cost' => Type\optional(Type\int()),
                            'memory_cost' => Type\optional(Type\int()),
                            'threads' => Type\optional(Type\int())
                        ])),
                    ]),
                )
            ))
        ]);
    }
}
