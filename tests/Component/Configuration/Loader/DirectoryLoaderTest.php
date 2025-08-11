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

namespace Neu\Tests\Component\Configuration\Loader;

use Neu\Component\DependencyInjection\Configuration\Loader\DirectoryLoader;
use Neu\Component\DependencyInjection\Configuration\Loader\JsonFileLoader;
use Neu\Component\DependencyInjection\Configuration\Loader\PhpFileLoader;
use Neu\Component\DependencyInjection\Configuration\Resolver\Resolver;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DirectoryLoaderTest extends TestCase
{
    public function testLoadFile(): void
    {
        $loader = new DirectoryLoader();
        $resolver = new Resolver([new PhpFileLoader(), new JsonFileLoader(), $loader]);

        $resource = __DIR__ . '/../Resources/config/';

        static::assertSame($loader, $resolver->resolve($resource));

        $configuration = $resolver->resolve($resource)->load($resource);

        static::assertTrue($configuration->has('foo'));
        static::assertSame(['bar' => true, 'baz' => false], $configuration->get('foo'));
        static::assertTrue($configuration->has('format'));
        static::assertCount(1, $configuration->get('format'));
        static::assertContains('php', $configuration->get('format'));
    }

    public function testLoadWithoutResolver(): void
    {
        $loader = new DirectoryLoader();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Resolver has not been set on the "' . DirectoryLoader::class . '" loader, make sure to call "' . DirectoryLoader::class . '::setResolver()" before attempting to load resources.'
        );

        $loader->load(__DIR__ . '/../Resources/config/');
    }

    #[DataProvider('getSupportCases')]
    public function testSupport(mixed $resource, bool $supported): void
    {
        $loader = new DirectoryLoader();

        static::assertSame($supported, $loader->supports($resource));
    }

    public static function getSupportCases(): iterable
    {
        return [
            [__DIR__, true],
            [__FILE__, false],
            ['file.php', false],
            ['file.php5', false],
            ['file.php7', false],
            ['file.json', false],
            ['file.js', false],
            ['file.yaml', false],
            ['', false],
            [[], false],
            [false, false],
            [null, false],
        ];
    }
}
