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

namespace Neu\Component\Cache\Driver;

use Amp\File;
use Generator;
use Neu\Component\Cache\Exception\InvalidKeyException;
use Neu\Component\Cache\Exception\RuntimeException;
use Neu\Component\Cache\Exception\UnavailableItemException;
use Psl\Encoding;
use Psl\Filesystem;
use Psl\Hash;
use Psl\Str;
use Override;

use function time;

final class FilesystemDriver extends AbstractDriver
{
    use PackingTrait;

    private readonly string $directory;

    /**
     * Creates a new {@see FilesystemDriver}.
     *
     * @param non-empty-string $directory The directory to store cache files.
     * @param positive-int $pruneInterval The interval, in seconds, at which to run {@see DriverInterface::prune()}.
     */
    public function __construct(string $directory, int $pruneInterval = self::PRUNE_INTERVAL)
    {
        if (!File\isDirectory($directory)) {
            File\createDirectoryRecursively($directory);
        }

        $this->directory = Filesystem\canonicalize($directory) ?? $directory;

        parent::__construct($pruneInterval);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function get(string $key): mixed
    {
        try {
            $filename = $this->getFile($key);
            if (!File\isFile($filename)) {
                throw UnavailableItemException::for($key);
            }

            return $this->unpack($key, File\read($filename));
        } catch (File\FilesystemException $e) {
            throw new RuntimeException('A filesystem error occurred while reading the cache.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function set(string $key, mixed $value, null|int $ttl = null): void
    {
        try {
            $filename = $this->getFile($key);

            File\write($filename, $this->pack($key, $value, $ttl));
        } catch (File\FilesystemException $e) {
            throw new RuntimeException('A filesystem error occurred while writing the cache.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function delete(string $key): void
    {
        try {
            $filename = $this->getFile($key);
            if (File\isFile($filename)) {
                File\deleteFile($filename);
            }
        } catch (File\FilesystemException $e) {
            throw new RuntimeException('A filesystem error occurred while deleting the cache.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function clear(): void
    {
        try {
            foreach ($this->listCacheFiles() as $filename) {
                File\deleteFile($filename);
            }
        } catch (File\FilesystemException $e) {
            throw new RuntimeException('A filesystem error occurred while clearing the cache.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function prune(): void
    {
        try {
            foreach ($this->listCacheFiles() as $filename) {
                $file = File\openFile($filename, 'r');
                $bytes = (string) $file->read(length: self::HEADER_BYTES_LENGTH);
                $file->close();
                if ($bytes === '') {
                    File\deleteFile($filename);

                    continue;
                }

                ['expires' => $expires] = $this->readHeader($bytes);
                if ($expires !== null && $expires < time()) {
                    File\deleteFile($filename);
                }
            }
        } catch (File\FilesystemException $e) {
            throw new RuntimeException('A filesystem error occurred while pruning the cache.', 0, $e);
        }
    }

    /**
     * The following method was copied from `symfony/cache` and modified to fit the needs of this implementation.
     *
     * @see https://github.com/symfony/cache/blob/60c8006e4bfa8b494b5995b38881ae344d8f6c41/Traits/FilesystemCommonTrait.php#L122
     *
     * @license MIT https://github.com/symfony/cache/blob/60c8006e4bfa8b494b5995b38881ae344d8f6c41/LICENSE
     * @copyright Copyright (c) 2016-present Fabien Potencier
     *
     * @param non-empty-string $key The key of the cache file.
     *
     * @throws InvalidKeyException If the key cannot be hashed.
     *
     * @return non-empty-string The path to the cache file.
     */
    private function getFile(string $key): string
    {
        try {
            $hash = Hash\hash(static::class . $key, Hash\Algorithm::Xxh128);
        } catch (Hash\Exception\ExceptionInterface $e) {
            throw new InvalidKeyException('Failed to hash the key.', 0, $e);
        }

        $hash = Encoding\Base64\encode($hash, Encoding\Base64\Variant::UrlSafe, padding: false);
        $directory = $this->directory . Filesystem\SEPARATOR . Str\Byte\uppercase($hash[0] . Filesystem\SEPARATOR . $hash[1]);
        if (!File\isDirectory($directory)) {
            File\createDirectoryRecursively($directory);
        }

        return $directory . Filesystem\SEPARATOR . Str\Byte\slice($hash, 2, 20);
    }

    /**
     * The following method was copied from `symfony/cache` and modified to fit the needs of this implementation.
     *
     * @see https://github.com/symfony/cache/blob/60c8006e4bfa8b494b5995b38881ae344d8f6c41/Traits/FilesystemCommonTrait.php#L140
     *
     * @license MIT https://github.com/symfony/cache/blob/60c8006e4bfa8b494b5995b38881ae344d8f6c41/LICENSE
     * @copyright Copyright (c) 2016-present Fabien Potencier
     *
     * @return iterable<string> A generator of cache files.
     */
    private function listCacheFiles(): iterable
    {
        $chars = '+-ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        for ($i = 0; $i < 38; ++$i) {
            $directory = $this->directory . Filesystem\SEPARATOR . $chars[$i] . Filesystem\SEPARATOR;
            if (!File\isDirectory($directory)) {
                continue;
            }

            for ($j = 0; $j < 38; ++$j) {
                $subDirectory = $directory . $chars[$j];
                if (!File\isDirectory($subDirectory)) {
                    continue;
                }

                foreach (File\listFiles($subDirectory) as $file) {
                    yield $subDirectory . Filesystem\SEPARATOR . $file;
                }
            }
        }
    }
}
