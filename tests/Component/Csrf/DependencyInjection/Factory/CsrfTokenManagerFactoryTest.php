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

namespace Neu\Tests\Component\Csrf\DependencyInjection\Factory;

use Neu\Component\Csrf\CsrfTokenManager;
use Neu\Component\Csrf\DependencyInjection\Factory\CsrfTokenManagerFactory;
use Neu\Component\Csrf\Generator\CsrfTokenGeneratorInterface;
use Neu\Component\Csrf\Storage\CsrfTokenStorageInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;

final class CsrfTokenManagerFactoryTest extends TestCase
{
    public function testCreateWithDefaults(): void
    {
        $generator = $this->createMock(CsrfTokenGeneratorInterface::class);
        $storage = $this->createMock(CsrfTokenStorageInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::exactly(2))
            ->method('getTyped')
            ->willReturnMap([
                [CsrfTokenGeneratorInterface::class, CsrfTokenGeneratorInterface::class, $generator],
                [CsrfTokenStorageInterface::class, CsrfTokenStorageInterface::class, $storage],
            ]);

        $factory = new CsrfTokenManagerFactory();
        $manager = $factory($container);

        static::assertInstanceOf(CsrfTokenManager::class, $manager);
    }

    public function testCreateWithCustomGeneratorAndStorage(): void
    {
        $generator = $this->createMock(CsrfTokenGeneratorInterface::class);
        $storage = $this->createMock(CsrfTokenStorageInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::exactly(2))
            ->method('getTyped')
            ->willReturnMap([
                ['custom_generator', CsrfTokenGeneratorInterface::class, $generator],
                ['custom_storage', CsrfTokenStorageInterface::class, $storage],
            ]);

        $factory = new CsrfTokenManagerFactory('custom_generator', 'custom_storage');
        $manager = $factory($container);

        static::assertInstanceOf(CsrfTokenManager::class, $manager);
    }
}
