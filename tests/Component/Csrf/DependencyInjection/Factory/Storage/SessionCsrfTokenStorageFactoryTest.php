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

namespace Neu\Tests\Component\Csrf\DependencyInjection\Factory\Storage;

use Neu\Component\Csrf\DependencyInjection\Factory\Storage\SessionCsrfTokenStorageFactory;
use Neu\Component\Csrf\Storage\SessionCsrfTokenStorage;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\Request;
use Neu\Component\Http\Session\Session;
use PHPUnit\Framework\TestCase;

final class SessionCsrfTokenStorageFactoryTest extends TestCase
{
    public function testCreateWithDefaults(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::never())->method(static::anything());

        $factory = new SessionCsrfTokenStorageFactory();
        $storage = $factory($container);

        static::assertInstanceOf(SessionCsrfTokenStorage::class, $storage);

        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $storage->setToken($request, 'csrf', 'token');

        static::assertSame('token', $request->getSession()->get(SessionCsrfTokenStorage::DEFAULT_PREFIX . ':csrf'));
    }

    public function testCreateWithCustomPrefix(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::never())->method(static::anything());

        $factory = new SessionCsrfTokenStorageFactory('custom_prefix');
        $storage = $factory($container);

        static::assertInstanceOf(SessionCsrfTokenStorage::class, $storage);

        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $storage->setToken($request, 'csrf', 'token');

        static::assertSame('token', $request->getSession()->get('custom_prefix:csrf'));
    }
}
