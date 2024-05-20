<?php

declare(strict_types=1);

namespace Neu\Component\Cache\DependencyInjection\Factory\Driver;

use Neu\Component\Cache\Driver\AbstractDriver;
use Neu\Component\Cache\Driver\FilesystemDriver;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\InvalidArgumentException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Psl\Str;

/**
 * A factory for creating a {@see FilesystemDriver} instance.
 *
 * @implements FactoryInterface<FilesystemDriver>
 */
final readonly class FilesystemDriverFactory implements FactoryInterface
{
    private string $directory;
    private int $pruneInterval;

    /**
     * @param string $directory The directory to store the cache files.
     * @param int $pruneInterval The interval in seconds to prune the cache.
     */
    public function __construct(string $directory, int $pruneInterval = AbstractDriver::PRUNE_INTERVAL)
    {
        $this->directory = $directory;
        $this->pruneInterval = $pruneInterval;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): FilesystemDriver
    {
        if (Str\contains($this->directory, "\0")) {
            throw new InvalidArgumentException('The directory path contains invalid characters.');
        }

        return new FilesystemDriver($this->directory, $this->pruneInterval);
    }
}
