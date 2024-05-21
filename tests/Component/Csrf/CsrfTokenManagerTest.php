<?php

declare(strict_types=1);

namespace Neu\Tests\Component\Csrf;

use Neu\Component\Csrf\CsrfTokenManager;
use Neu\Component\Csrf\Exception\TokenNotFoundException;
use Neu\Component\Csrf\Storage\SessionCsrfTokenStorage;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\Request;
use Neu\Component\Http\Session\Session;
use PHPUnit\Framework\TestCase;

final class CsrfTokenManagerTest extends TestCase
{
    public function testGetToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $manager = new CsrfTokenManager();

        $token = $manager->getOrCreateToken($request, 'csrf');

        static::assertNotEmpty($token);

        $retrievedToken = $manager->getToken($request, 'csrf');

        static::assertSame($token, $retrievedToken);
    }

    public function testGetThrowsForUnavailableToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $manager = new CsrfTokenManager();

        $this->expectException(TokenNotFoundException::class);
        $this->expectExceptionMessage('The CSRF token for the identifier "csrf" was not found.');

        $manager->getToken($request, 'csrf');
    }

    public function testGetOrCreateToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));

        $storage = new SessionCsrfTokenStorage();
        $manager = new CsrfTokenManager(storage: $storage);

        static::assertFalse($storage->hasToken($request, 'csrf'));

        $token = $manager->getOrCreateToken($request, 'csrf');

        static::assertNotEmpty($token);
        static::assertTrue($storage->hasToken($request, 'csrf'));
        static::assertSame($token, $storage->getToken($request, 'csrf'));
    }

    public function testRotateToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $manager = new CsrfTokenManager();

        $originalToken = $manager->getOrCreateToken($request, 'csrf');
        $newToken = $manager->rotateToken($request, 'csrf');

        static::assertNotSame($originalToken, $newToken);
        static::assertSame($newToken, $manager->getOrCreateToken($request, 'csrf'));
    }

    public function testRemoveToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $manager = new CsrfTokenManager();
        $manager->getOrCreateToken($request, 'csrf');

        $manager->removeToken($request, 'csrf');

        $this->expectException(TokenNotFoundException::class);
        $this->expectExceptionMessage('The CSRF token for the identifier "csrf" was not found.');

        $manager->getToken($request, 'csrf');
    }

    public function testValidateTokenSuccess(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $manager = new CsrfTokenManager();

        $token = $manager->getOrCreateToken($request, 'csrf');

        static::assertTrue($manager->validateToken($request, 'csrf', $token));
    }

    public function testValidateTokenFailureWrongValue(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $manager = new CsrfTokenManager();

        $manager->getOrCreateToken($request, 'csrf');

        static::assertFalse($manager->validateToken($request, 'csrf', 'invalid_token'));
    }

    public function testValidateTokenFailureMissingToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $manager = new CsrfTokenManager();

        static::assertFalse($manager->validateToken($request, 'csrf', 'any_value'));
    }
}
