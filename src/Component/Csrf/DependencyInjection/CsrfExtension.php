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

namespace Neu\Component\Csrf\DependencyInjection;

use Neu\Component\Csrf\CsrfTokenManager;
use Neu\Component\Csrf\CsrfTokenManagerInterface;
use Neu\Component\Csrf\DependencyInjection\Factory\CsrfTokenManagerFactory;
use Neu\Component\Csrf\DependencyInjection\Factory\Generator\UrlSafeCsrfTokenGeneratorFactory;
use Neu\Component\Csrf\DependencyInjection\Factory\Storage\SessionCsrfTokenStorageFactory;
use Neu\Component\Csrf\Generator\CsrfTokenGeneratorInterface;
use Neu\Component\Csrf\Generator\UrlSafeCsrfTokenGenerator;
use Neu\Component\Csrf\Storage\CsrfTokenStorageInterface;
use Neu\Component\Csrf\Storage\SessionCsrfTokenStorage;
use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\DependencyInjection\RegistryInterface;
use Psl\Type;

/**
 * The extension for the CSRF component.
 *
 * @psalm-type Configuration = array{
 *     storage?: array{
 *         prefix?: non-empty-string,
 *     },
 *     manager?: array{
 *         generator?: non-empty-string,
 *         storage?: non-empty-string,
 *     }
 * }
 */
final readonly class CsrfExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function register(RegistryInterface $registry, DocumentInterface $configurations): void
    {
        $configuration = $configurations->getOfTypeOrDefault('csrf', $this->getConfigurationType(), []);

        $registry->addDefinition(Definition::ofType(UrlSafeCsrfTokenGenerator::class, new UrlSafeCsrfTokenGeneratorFactory()));
        $registry->addDefinition(Definition::ofType(SessionCsrfTokenStorage::class, new SessionCsrfTokenStorageFactory(
            $configuration['storage']['prefix'] ?? null,
        )));
        $registry->addDefinition(Definition::ofType(CsrfTokenManager::class, new CsrfTokenManagerFactory(
            $configuration['manager']['generator'] ?? null,
            $configuration['manager']['storage'] ?? null,
        )));

        $registry->getDefinition(UrlSafeCsrfTokenGenerator::class)->addAlias(CsrfTokenGeneratorInterface::class);
        $registry->getDefinition(SessionCsrfTokenStorage::class)->addAlias(CsrfTokenStorageInterface::class);
        $registry->getDefinition(CsrfTokenManager::class)->addAlias(CsrfTokenManagerInterface::class);
    }

    /**
     * Returns the configuration type for the CSRF component.
     *
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'storage' => Type\optional(Type\shape([
                'prefix' => Type\optional(Type\non_empty_string()),
            ])),
            'manager' => Type\optional(Type\shape([
                'generator' => Type\optional(Type\non_empty_string()),
                'storage' => Type\optional(Type\non_empty_string()),
            ])),
        ]);
    }
}
