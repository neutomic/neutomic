<?php

declare(strict_types=1);

namespace Neu\Tests\Component\Csrf\DependencyInjection;

use Neu\Component\Configuration\Exception\InvalidEntryException;
use Neu\Component\Csrf\CsrfTokenManager;
use Neu\Component\Csrf\CsrfTokenManagerInterface;
use Neu\Component\Csrf\DependencyInjection\CsrfExtension;
use Neu\Component\Csrf\Generator\CsrfTokenGeneratorInterface;
use Neu\Component\Csrf\Generator\UrlSafeCsrfTokenGenerator;
use Neu\Component\Csrf\Storage\CsrfTokenStorageInterface;
use Neu\Component\Csrf\Storage\SessionCsrfTokenStorage;
use Neu\Component\DependencyInjection\ContainerBuilder;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\Request;
use Neu\Component\Http\Session\Session;
use PHPUnit\Framework\TestCase;

final class CsrfExtensionTest extends TestCase
{
    public function testRegister(): void
    {
        $project = Project::create(__DIR__, __FILE__);
        $builder = new ContainerBuilder($project);

        $extension = new CsrfExtension();
        $extension->register($builder);

        static::assertTrue($builder->hasDefinition(CsrfTokenManager::class));
        static::assertTrue($builder->hasDefinition(SessionCsrfTokenStorage::class));
        static::assertTrue($builder->hasDefinition(UrlSafeCsrfTokenGenerator::class));

        static::assertContains(CsrfTokenManagerInterface::class, $builder->getDefinition(CsrfTokenManager::class)->getAliases());
        static::assertContains(CsrfTokenGeneratorInterface::class, $builder->getDefinition(UrlSafeCsrfTokenGenerator::class)->getAliases());
        static::assertContains(CsrfTokenStorageInterface::class, $builder->getDefinition(SessionCsrfTokenStorage::class)->getAliases());
    }

    public function testConfigurations(): void
    {
        $project = Project::create(__DIR__, __FILE__);
        $builder = new ContainerBuilder($project);
        $builder->addConfiguration([
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

        $extension = new CsrfExtension();
        $extension->register($builder);

        static::assertTrue($builder->hasDefinition(CsrfTokenManager::class));
        static::assertTrue($builder->hasDefinition(SessionCsrfTokenStorage::class));
        static::assertTrue($builder->hasDefinition(UrlSafeCsrfTokenGenerator::class));

        static::assertContains(CsrfTokenManagerInterface::class, $builder->getDefinition(CsrfTokenManager::class)->getAliases());
        static::assertContains(CsrfTokenGeneratorInterface::class, $builder->getDefinition(UrlSafeCsrfTokenGenerator::class)->getAliases());
        static::assertContains(CsrfTokenStorageInterface::class, $builder->getDefinition(SessionCsrfTokenStorage::class)->getAliases());

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::exactly(2))
            ->method('getTyped')
            ->willReturnMap([
                ['custom_generator', CsrfTokenGeneratorInterface::class, $this->createMock(CsrfTokenGeneratorInterface::class)],
                ['custom_storage', CsrfTokenStorageInterface::class, $this->createMock(CsrfTokenStorageInterface::class)],
            ]);

        $builder->getDefinition(CsrfTokenManager::class)->resolve($container);

        $storage = $builder->getDefinition(SessionCsrfTokenStorage::class)->resolve($container);

        $request = Request::create(Method::Get, '/')->withSession(new Session([]));

        $storage->setToken($request, 'csrf', 'token');

        static::assertSame('token', $request->getSession()->get('custom_prefix:csrf'));
    }

    public function testInvalidStoragePrefixConfiguration(): void
    {
        $project = Project::create(__DIR__, __FILE__);
        $builder = new ContainerBuilder($project);
        $builder->addConfiguration([
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

        $this->expectException(InvalidEntryException::class);

        $extension = new CsrfExtension();
        $extension->register($builder);
    }

    public function testInvalidManagerConfiguration(): void
    {
        $project = Project::create(__DIR__, __FILE__);
        $builder = new ContainerBuilder($project);
        $builder->addConfiguration([
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

        $this->expectException(InvalidEntryException::class);

        $extension = new CsrfExtension();
        $extension->register($builder);
    }
}
