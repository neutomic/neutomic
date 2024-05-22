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

use Neu\Component\Configuration\Exception\InvalidConfigurationException;
use Neu\Component\Configuration\Loader\PHPFileLoader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PHPFileLoaderTest extends TestCase
{
    public function testLoadFile(): void
    {
        $loader = new PHPFileLoader();
        $configuration = $loader->load(__DIR__ . '/../Resources/config/configuration.php');

        static::assertTrue($configuration->has('foo'));
        static::assertSame(['bar' => true, 'baz' => false], $configuration->get('foo'));
    }

    public function testLoadFileFails(): void
    {
        $loader = new PHPFileLoader();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Failed to coerce php resource file');

        $loader->load(__DIR__ . '/../Resources/invalid-config/configuration.invalid.php');
    }


    #[DataProvider('getSupportCases')]
    public function testSupport(mixed $resource, bool $supported): void
    {
        $loader = new PHPFileLoader();

        static::assertSame($supported, $loader->supports($resource));
    }

    public static function getSupportCases(): iterable
    {
        return [
            [__DIR__ . '/../Resources/config/configuration.php', true],
            [__DIR__ . '/../Resources/config/configuration.json', false],
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
