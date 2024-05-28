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

namespace Neu\Tests\Component\Http\Router\Generator;

use Neu\Component\Http\Exception\InvalidArgumentException;
use Neu\Component\Http\Exception\OutOfBoundsException;
use Neu\Component\Http\Exception\UnexpectedValueException;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Router\Generator\Generator;
use Neu\Component\Http\Router\Registry\Registry;
use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GeneratorTest extends TestCase
{
    public static function provideGeneratingRoute(): iterable
    {
        // no parameters
        yield ['', [], ''];
        yield ['/', [], '/'];
        yield ['/foo', [], '/foo'];
        // simple parameter
        yield ['/foo/{id}', ['id' => 1], '/foo/1'];
        yield ['/foo/{id}', ['id' => '1'], '/foo/1'];
        yield ['/foo/{id}', ['id' => 'foo'], '/foo/foo'];
        yield ['/foo/{id}', ['id' => 'foo/bar'], '/foo/foo%2Fbar'];
        // multiple parameters
        yield ['/foo/{id}/{name}', ['id' => 1, 'name' => 'bar'], '/foo/1/bar'];
        yield ['/foo/{id}/{name}', ['id' => '1', 'name' => 'bar'], '/foo/1/bar'];
        yield ['/foo/{id}/{name}', ['id' => 'foo', 'name' => 'bar'], '/foo/foo/bar'];
        // regex parameter
        yield ['/foo/{id:\d+}', ['id' => 1], '/foo/1'];
        yield ['/foo/{id:\d+}', ['id' => '1'], '/foo/1'];
        yield ['/foo/{id:(foo|bar)}', ['id' => 'foo'], '/foo/foo'];
        yield ['/foo/{id:(foo|bar)}', ['id' => 'bar'], '/foo/bar'];
        // multiple regex parameters
        yield ['/foo/{id:\d+}/{name:\w+}', ['id' => 1, 'name' => 'bar'], '/foo/1/bar'];
        yield ['/foo/{id:\d+}/{name:\w+}', ['id' => '1', 'name' => 'bar'], '/foo/1/bar'];
        yield ['/foo/{id:(foo|bar)}/{name:(baz|qux)}', ['id' => 'foo', 'name' => 'baz'], '/foo/foo/baz'];
        yield ['/foo/{id:(foo|bar)}/{name:(baz|qux)}', ['id' => 'bar', 'name' => 'qux'], '/foo/bar/qux'];
        // optional parameter
        yield ['/foo[/{id}]', [], '/foo'];
        yield ['/foo/[{id}/]', ['id' => 1], '/foo/1/'];
        yield ['/foo/[{id}/]', ['id' => '1'], '/foo/1/'];
        yield ['/foo/[{id}/]', ['id' => 'foo'], '/foo/foo/'];
        // optional regex parameter
        yield ['/foo[/{id:\d+}]', [], '/foo'];
        yield ['/foo[/{id:\d+}]', ['id' => 1], '/foo/1'];
        yield ['/foo[/{id:\d+}]', ['id' => '1'], '/foo/1'];
        // optional multiple parameters
        yield ['/foo[/{id}/{name}]', [], '/foo'];
        yield ['/foo[/{id}/{name}]', ['id' => 1, 'name' => 'bar'], '/foo/1/bar'];
        yield ['/foo[/{id}/{name}]', ['id' => '1', 'name' => 'bar'], '/foo/1/bar'];
        yield ['/foo[/{id}/{name}]', ['id' => 'foo', 'name' => 'bar'], '/foo/foo/bar'];
        // optional regex multiple parameters
        yield ['/foo[/{id:\d+}/{name:\w+}]', [], '/foo'];
        yield ['/foo[/{id:\d+}/{name:\w+}]', ['id' => 1, 'name' => 'bar'], '/foo/1/bar'];
        yield ['/foo[/{id:\d+}/{name:\w+}]', ['id' => '1', 'name' => 'bar'], '/foo/1/bar'];
        yield ['/foo[/{id:\d+}/{name:\w+}]', ['id' => '12', 'name' => 'bar'], '/foo/12/bar'];
        // optional between parameters
        yield ['/foo/{id}[/{name}]/{age}', ['id' => 1, 'age' => 18], '/foo/1/18'];
        yield ['/foo/{id}[/{name}]/{age}', ['id' => 1, 'name' => 'bar', 'age' => 18], '/foo/1/bar/18'];
        yield ['/foo/{id}[/{name}]/{age}', ['id' => 1, 'name' => 'bar', 'age' => 18], '/foo/1/bar/18'];
        yield ['/foo/{id}[/{name}]/{age}', ['id' => 1, 'name' => 'bar', 'age' => 18], '/foo/1/bar/18'];
        yield ['/foo/{id}/[{name}]/{age}', ['id' => 1, 'age' => 18], '/foo/1//18'];
        yield ['/foo/{id}/[{name}]/{age}', ['id' => 1, 'name' => 'bar', 'age' => 18], '/foo/1/bar/18'];
        // optional between regex parameters
        yield ['/foo/{id:\d+}[/{name:\w+}]/{age:\d+}', ['id' => 1, 'age' => 18], '/foo/1/18'];
        yield ['/foo/{id:\d+}[/{name:\w+}]/{age:\d+}', ['id' => 1, 'name' => 'bar', 'age' => 18], '/foo/1/bar/18'];
    }

    #[DataProvider('provideGeneratingRoute')]
    public function testGeneratingRoute(string $pattern, array $parameters, string $expected): void
    {
        $registry = new Registry();

        $route = new Route('name', $pattern, [Method::Get], 0);
        $registry->register($route, $this->createMock(HandlerInterface::class));

        $generator = new Generator($registry);

        static::assertSame($expected, $generator->generate('name', $parameters)->toString());
    }

    public function testFailsIfRouteNotFound(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Route "name" was not found.');

        $generator = new Generator(new Registry());
        $generator->generate('name');
    }

    public function testSuggestsAlternativeRoutes(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Route "fox" was not found, did you mean "foo"?');

        $registry = new Registry();

        $route = new Route('foo', '/foo', [Method::Get], 0);
        $registry->register($route, $this->createMock(HandlerInterface::class));

        $generator = new Generator($registry);
        $generator->generate('fox');
    }

    public function testSuggestsMultipleAlternativeRoutes(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Route "fox" was not found, did you mean one of the following: "foo", "fow", "for"?');

        $registry = new Registry();

        $route = new Route('foo', '/foo', [Method::Get], 0);
        $registry->register($route, $this->createMock(HandlerInterface::class));

        $route = new Route('fow', '/fow', [Method::Get], 0);
        $registry->register($route, $this->createMock(HandlerInterface::class));

        $route = new Route('for', '/for', [Method::Get], 0);
        $registry->register($route, $this->createMock(HandlerInterface::class));

        $generator = new Generator($registry);
        $generator->generate('fox');
    }

    public function testThrowsForMissingParameters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameter "id" for route "name".');

        $registry = new Registry();

        $route = new Route('name', '/foo/{id}', [Method::Get], 0);
        $registry->register($route, $this->createMock(HandlerInterface::class));

        $generator = new Generator($registry);
        $generator->generate('name');
    }

    public function testParameterDoesNotMatchRegex(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Parameter "id" for route "name" does not match the expected pattern "\d+".');

        $registry = new Registry();

        $route = new Route('name', '/foo/{id:\d+}', [Method::Get], 0);
        $registry->register($route, $this->createMock(HandlerInterface::class));

        $generator = new Generator($registry);
        $generator->generate('name', ['id' => 'foo']);
    }

    public function testParameterDoesNotMatchRegexWithMultipleParameters(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Parameter "id" for route "name" does not match the expected pattern "\d+".');

        $registry = new Registry();

        $route = new Route('name', '/foo/{id:\d+}/{name}', [Method::Get], 0);
        $registry->register($route, $this->createMock(HandlerInterface::class));

        $generator = new Generator($registry);
        $generator->generate('name', ['id' => 'foo', 'name' => 'bar']);
    }
}
