<?php

declare(strict_types=1);

namespace Neu\Tests\Component\Csrf\Storage;

use Neu\Component\Csrf\Exception\TokenNotFoundException;
use Neu\Component\Csrf\Storage\SessionCsrfTokenStorage;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\Request;
use Neu\Component\Http\Session\Session;
use PHPUnit\Framework\TestCase;

final class SessionCsrfTokenStorageTest extends TestCase
{
    public function testHasTokenWithExistingToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $storage = new SessionCsrfTokenStorage();

        $identifier = 'test_token';
        $tokenValue = 'token_value';
        $storage->setToken($request, $identifier, $tokenValue);

        static::assertTrue($storage->hasToken($request, $identifier));
    }

    public function testHasTokenWithNonExistingToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $storage = new SessionCsrfTokenStorage();

        static::assertFalse($storage->hasToken($request, 'non_existent_token'));
    }

    public function testGetTokenWithExistingToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $storage = new SessionCsrfTokenStorage();

        $identifier = 'test_token';
        $tokenValue = 'token_value';
        $storage->setToken($request, $identifier, $tokenValue);

        static::assertSame($tokenValue, $storage->getToken($request, $identifier));
    }

    public function testGetTokenWithNonExistingToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $storage = new SessionCsrfTokenStorage();

        $this->expectException(TokenNotFoundException::class);
        $storage->getToken($request, 'non_existent_token');
    }

    public function testSetToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $storage = new SessionCsrfTokenStorage();

        $identifier = 'test_token';
        $tokenValue = 'token_value';

        $storage->setToken($request, $identifier, $tokenValue);

        static::assertTrue($request->getSession()->has('neu-csrf:' . $identifier));
        static::assertSame($tokenValue, $request->getSession()->get('neu-csrf:' . $identifier));
    }

    public function testRemoveToken(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $storage = new SessionCsrfTokenStorage();

        $identifier = 'test_token';
        $tokenValue = 'token_value';
        $storage->setToken($request, $identifier, $tokenValue);

        $storage->removeToken($request, $identifier);
        static::assertFalse($storage->hasToken($request, $identifier));
    }

    public function testClear(): void
    {
        $request = Request::create(Method::Get, '/')->withSession(new Session([]));
        $storage = new SessionCsrfTokenStorage();

        $storage->setToken($request, 'token1', 'value1');
        $storage->setToken($request, 'token2', 'value2');

        $storage->clear($request);

        static::assertFalse($storage->hasToken($request, 'token1'));
        static::assertFalse($storage->hasToken($request, 'token2'));
    }
}
