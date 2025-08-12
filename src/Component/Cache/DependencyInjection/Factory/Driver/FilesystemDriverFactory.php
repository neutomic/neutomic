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
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class FilesystemDriverFactory implements FactoryInterface
{
    /**
     * The directory to store the cache files.
     *
     * @var non-empty-string
     */
    private string $directory;

    /**
     * The interval in seconds to prune the cache.
     *
     * @var positive-int
     */
    private int $pruneInterval;

    /**
     * @param non-empty-string $directory The directory to store the cache files.
     * @param positive-int $pruneInterval The interval in seconds to prune the cache.
     */
    public function __construct(string $directory, null|int $pruneInterval = null)
    {
        $this->directory = $directory;
        $this->pruneInterval = $pruneInterval ?? AbstractDriver::PRUNE_INTERVAL;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): FilesystemDriver
    {
        if (Str\contains($this->directory, "\0")) {
            throw new InvalidArgumentException('The directory path contains invalid characters.');
        }

        return new FilesystemDriver($this->directory, $this->pruneInterval);
    }
}
