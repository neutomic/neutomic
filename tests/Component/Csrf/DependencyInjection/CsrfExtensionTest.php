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

namespace Neu\Tests\Component\Csrf\DependencyInjection;

use Neu\Component\Csrf\CsrfTokenManager;
use Neu\Component\Csrf\CsrfTokenManagerInterface;
use Neu\Component\Csrf\DependencyInjection\CsrfExtension;
use Neu\Component\Csrf\Generator\CsrfTokenGeneratorInterface;
use Neu\Component\Csrf\Generator\UrlSafeCsrfTokenGenerator;
use Neu\Component\Csrf\Storage\CsrfTokenStorageInterface;
use Neu\Component\Csrf\Storage\SessionCsrfTokenStorage;
use Neu\Component\DependencyInjection\ContainerBuilder;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\InvalidEntryException;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\Request;
use Neu\Component\Http\Session\Session;
use PHPUnit\Framework\TestCase;
use Psl\SecureRandom;

final class CsrfExtensionTest extends TestCase
{
    public function testRegister(): void
    {
        $project = Project::create(SecureRandom\string(32), __DIR__, __FILE__);
        $builder = ContainerBuilder::create($project);
        $builder->addExtension(new CsrfExtension());
        $builder->build();

        $registry = $builder->getRegistry();

        static::assertTrue($registry->hasDefinition(CsrfTokenManager::class));
        static::assertTrue($registry->hasDefinition(SessionCsrfTokenStorage::class));
        static::assertTrue($registry->hasDefinition(UrlSafeCsrfTokenGenerator::class));

        static::assertContains(CsrfTokenManagerInterface::class, $registry->getDefinition(CsrfTokenManager::class)->getAliases());
        static::assertContains(CsrfTokenStorageInterface::class, $registry->getDefinition(SessionCsrfTokenStorage::class)->getAliases());
        static::assertContains(CsrfTokenGeneratorInterface::class, $registry->getDefinition(UrlSafeCsrfTokenGenerator::class)->getAliases());
    }

    public function testConfigurations(): void
    {
        $project = Project::create(SecureRandom\string(32), __DIR__, __FILE__);
        $builder = ContainerBuilder::create($project);
        $builder->addExtension(new CsrfExtension());
        $builder->addConfigurationResource([
            'csrf' => [
                'storage' => [
                    'prefix' => 'custom_prefix'
                ],
                'manager' => [
                    'generator' => 'custom_generator',
                    'storage' => 'custom_storage'
                ]
            ]
        ]);

        $builder->addExtension(new CsrfExtension());
        $builder->build();

        $registry = $builder->getRegistry();

        static::assertTrue($registry->hasDefinition(CsrfTokenManager::class));
        static::assertTrue($registry->hasDefinition(SessionCsrfTokenStorage::class));
        static::assertTrue($registry->hasDefinition(UrlSafeCsrfTokenGenerator::class));

        static::assertContains(CsrfTokenManagerInterface::class, $registry->getDefinition(CsrfTokenManager::class)->getAliases());
        static::assertContains(CsrfTokenStorageInterface::class, $registry->getDefinition(SessionCsrfTokenStorage::class)->getAliases());
        static::assertContains(CsrfTokenGeneratorInterface::class, $registry->getDefinition(UrlSafeCsrfTokenGenerator::class)->getAliases());

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::exactly(2))
            ->method('getTyped')
            ->willReturnMap([
                ['custom_generator', CsrfTokenGeneratorInterface::class, $this->createMock(CsrfTokenGeneratorInterface::class)],
                ['custom_storage', CsrfTokenStorageInterface::class, $this->createMock(CsrfTokenStorageInterface::class)],
            ]);

        $registry->getDefinition(CsrfTokenManager::class)->resolve($container);

        $storage = $registry->getDefinition(SessionCsrfTokenStorage::class)->resolve($container);

        $request = Request::create(Method::Get, '/')->withSession(new Session([]));

        $storage->setToken($request, 'csrf', 'token');

        static::assertSame('token', $request->getSession()->get('custom_prefix:csrf'));
    }

    public function testInvalidStoragePrefixConfiguration(): void
    {
        $project = Project::create(SecureRandom\string(32), __DIR__, __FILE__);
        $builder = ContainerBuilder::create($project);
        $builder->addExtension(new CsrfExtension());
        $builder->addConfigurationResource([
            'csrf' => [
                'storage' => [
                    'prefix' => ['invalid']
                ],
                'manager' => [
                    'generator' => 'custom_generator',
                    'storage' => 'custom_storage'
                ]
            ]
        ]);

        $builder->addExtension(new CsrfExtension());

        $this->expectException(InvalidEntryException::class);

        $builder->build();
    }

    public function testInvalidManagerConfiguration(): void
    {
        $project = Project::create(SecureRandom\string(32), __DIR__, __FILE__);
        $builder = ContainerBuilder::create($project);
        $builder->addExtension(new CsrfExtension());
        $builder->addConfigurationResource([
            'csrf' => [
                'storage' => [
                    'prefix' => 'custom_prefix'
                ],
                'manager' => [
                    'generator' => ['invalid'],
                    'storage' => 'custom_storage'
                ]
            ]
        ]);

        $builder->addExtension(new CsrfExtension());

        $this->expectException(InvalidEntryException::class);

        $builder->build();
    }
}
