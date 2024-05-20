<?php

declare(strict_types=1);

namespace Neu\Tests\Component\Configuration\Loader;

use Neu\Component\Configuration\Exception\InvalidConfigurationException;
use Neu\Component\Configuration\Loader\JsonFileLoader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class JsonFileLoaderTest extends TestCase
{
    public function testLoadFile(): void
    {
        $loader = new JsonFileLoader();
        $configuration = $loader->load(__DIR__ . '/../Resources/config/configuration.json');

        static::assertTrue($configuration->has('foo'));
        static::assertSame(['bar' => true, 'baz' => false], $configuration->get('foo'));
    }

    public function testLoadFileFails(): void
    {
        $loader = new JsonFileLoader();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Failed to decode json resource file "');

        $loader->load(__DIR__ . '/../Resources/invalid-config/configuration.invalid.json');
    }

    #[DataProvider('getSupportCases')]
    public function testSupport(mixed $resource, bool $supported): void
    {
        $loader = new JsonFileLoader();

        static::assertSame($supported, $loader->supports($resource));
    }

    public static function getSupportCases(): iterable
    {
        return [
            [__DIR__ . '/../Resources/config/configuration.json', true],
            [__DIR__ . '/../Resources/config/configuration.php', false],
            ['file.json', false],
            ['file.json5', false],
            ['file.php', false],
            ['file.js', false],
            ['file.yaml', false],
            ['', false],
            [[], false],
            [false, false],
            [null, false],
        ];
    }
}
