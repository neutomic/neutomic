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

namespace Neu\Tests\Component\Http\Message;

use Neu\Component\Http\Exception\InvalidArgumentException;
use Neu\Component\Http\Message\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UriTest extends TestCase
{
    /**
     * @param non-empty-string $url
     * @param array{scheme: string|null, host: string|null, port: int|null, path: string, query: string|null, fragment: string|null} $expected
     */
    #[DataProvider('provideUrls')]
    public function testFromUrl(string $url, array $expected): void
    {
        $uri = Uri::fromUrl($url);

        static::assertSame($url, (string) $uri);
        static::assertSame($expected['scheme'], $uri->getScheme());
        static::assertSame($expected['host'], $uri->getHost());
        static::assertSame($expected['port'], $uri->getPort());
        static::assertSame($expected['path'], $uri->getPath());
        static::assertSame($expected['query'], $uri->getQuery());
        static::assertSame($expected['fragment'], $uri->getFragment());
    }

    /**
     * @return array<int, array{
     *     0: non-empty-string,
     *     1: array{scheme: string|null, host: string|null, port: int|null, path: string, query: string|null, fragment: string|null},
     * }>
     */
    public static function provideUrls(): array
    {
        return [
            'full url' => [
                'https://example.com:8080/path?query#fragment',
                [
                    'scheme' => 'https',
                    'host' => 'example.com',
                    'port' => 8080,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment'
                ]
            ],
            'no host' => [
                '/path?query#fragment',
                [
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment'
                ]
            ],
            'no port' => [
                'https://example.com/path',
                [
                    'scheme' => 'https',
                    'host' => 'example.com',
                    'port' => null,
                    'path' => '/path',
                    'query' => null,
                    'fragment' => null
                ]
            ],
            'no query' => [
                'https://example.com/path#fragment',
                [
                    'scheme' => 'https',
                    'host' => 'example.com',
                    'port' => null,
                    'path' => '/path',
                    'query' => null,
                    'fragment' => 'fragment'
                ]
            ],
            'no fragment' => [
                'https://example.com/path?query',
                [
                    'scheme' => 'https',
                    'host' => 'example.com',
                    'port' => null,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => null
                ]
            ],
            'empty query' => [
                'https://example.com/path?',
                [
                    'scheme' => 'https',
                    'host' => 'example.com',
                    'port' => null,
                    'path' => '/path',
                    'query' => '',
                    'fragment' => null
                ]
            ],
            'empty fragment' => [
                'https://example.com/path#',
                [
                    'scheme' => 'https',
                    'host' => 'example.com',
                    'port' => null,
                    'path' => '/path',
                    'query' => null,
                    'fragment' => ''
                ]
            ],
            'no path' => [
                'https://example.com:8081?query#fragment',
                [
                    'scheme' => 'https',
                    'host' => 'example.com',
                    'port' => 8081,
                    'path' => '',
                    'query' => 'query',
                    'fragment' => 'fragment'
                ]
            ],
        ];
    }

    public function testWithScheme(): void
    {
        $uri = Uri::fromUrl('https://example.com');
        $newUri = $uri->withScheme('http');

        static::assertSame('http', $newUri->getScheme());
        static::assertSame('http://example.com', (string) $newUri);
    }

    public function testWithHost(): void
    {
        $uri = Uri::fromUrl('https://example.com');
        $newUri = $uri->withHost('example.org');

        static::assertSame('example.org', $newUri->getHost());
        static::assertSame('https://example.org', (string) $newUri);
    }

    public function testWithPort(): void
    {
        $uri = Uri::fromUrl('https://example.com');
        $newUri = $uri->withPort(8080);

        static::assertSame(8080, $newUri->getPort());
        static::assertSame('https://example.com:8080', (string) $newUri);
    }

    public function testWithPath(): void
    {
        $uri = Uri::fromUrl('https://example.com');
        $newUri = $uri->withPath('/path');

        static::assertSame('/path', $newUri->getPath());
        static::assertSame('https://example.com/path', (string) $newUri);
    }

    public function testWithQuery(): void
    {
        $uri = Uri::fromUrl('https://example.com');
        $newUri = $uri->withQuery('query');

        static::assertSame('query', $newUri->getQuery());
        static::assertSame('https://example.com?query', (string) $newUri);
    }

    public function testWithFragment(): void
    {
        $uri = Uri::fromUrl('https://example.com');
        $newUri = $uri->withFragment('fragment');

        static::assertSame('fragment', $newUri->getFragment());
        static::assertSame('https://example.com#fragment', (string) $newUri);
    }

    public function testWithUserInformation(): void
    {
        $uri = Uri::fromUrl('https://example.com');
        $newUri = $uri->withUserInformation('user', 'password');

        static::assertSame('user:password', $newUri->getUserInformation());
        static::assertSame('user:password@example.com', $newUri->getAuthority());
        static::assertSame('https://user:password@example.com', (string)$newUri);
    }

    public function testWithNullUserRemovesUserInformation(): void
    {
        $uri = Uri::fromUrl('https://user:password@example.com');
        $newUri = $uri->withUserInformation(null, null);

        static::assertNull($newUri->getUserInformation());
        static::assertSame('example.com', $newUri->getAuthority());
        static::assertSame('https://example.com', (string)$newUri);
    }

    public function testWithEmptyUserThrows(): void
    {
        $uri = Uri::fromUrl('https://user:password@example.com');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected user information to be non-empty.');

        $uri->withUserInformation('', 'password');
    }

    public function testWithEmptyPasswordThrows(): void
    {
        $uri = Uri::fromUrl('https://user:password@example.com');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected user information password to be non-empty.');

        $uri->withUserInformation('user', '');
    }

    public function testInvalidPortThrows(): void
    {
        $uri = Uri::fromUrl('https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The given port "-1" is invalid.');

        $uri->withPort(-1);
    }

    public function testInvalidSchemeThrows(): void
    {
        $uri = Uri::fromUrl('https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected scheme to be non-empty.');

        $uri->withScheme('');
    }

    public function testPortIsNormalized(): void
    {
        $uri = Uri::fromUrl('https://example.com:443');

        static::assertSame(null, $uri->getPort());
        static::assertSame('https://example.com', (string) $uri);
    }

    public function testNoPortAlwaysReturnsNull(): void
    {
        $uri = Uri::fromUrl('https://example.com');

        static::assertSame(null, $uri->getPort());
    }
}
