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

use Neu\Component\Cache\Exception\UnavailableItemException;

use function array_key_exists;
use function array_key_first;
use function count;
use function time;

final class LocalDriver extends AbstractDriver
{
    /**
     * The interval of which to run garbage collection to remove expired items.
     *
     * @var positive-int
     */
    public const int PRUNE_INTERVAL = 10;

    /**
     * The maximum number of items that can be held in cache at one time.
     *
     * @var null|positive-int
     */
    private readonly null|int $size;

    /**
     * @var array<non-empty-string, mixed>
     */
    private array $cache = [];

    /**
     * @var array<non-empty-string, int>
     */
    private array $cacheExpiration = [];

    /**
     * Creates a new local cache driver.
     *
     * @param positive-int $pruneInterval The interval, in seconds, at which to run {@see DriverInterface::prune()}.
     * @param null|positive-int $size The maximum number of items that can be held in cache at one time.
     */
    public function __construct(int $pruneInterval = self::PRUNE_INTERVAL, null|int $size = null)
    {
        parent::__construct($pruneInterval);

        $this->size = $size;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function get(string $key): mixed
    {
        if (array_key_exists($key, $this->cache)) {
            if (!array_key_exists($key, $this->cacheExpiration)) {
                return $this->cache[$key];
            }

            if (time() < $this->cacheExpiration[$key]) {
                return $this->cache[$key];
            }

            unset($this->cache[$key]);
        }

        throw UnavailableItemException::for($key);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function set(string $key, mixed $value, null|int $ttl = null): void
    {
        $this->cache[$key] = $value;
        if ($ttl !== null) {
            $this->cacheExpiration[$key] = time() + $ttl;
        }

        if (null !== $this->size && count($this->cache) === $this->size && $item = array_key_first($this->cache)) {
            $this->delete($item);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function delete(string $key): void
    {
        unset($this->cache[$key], $this->cacheExpiration[$key]);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function clear(): void
    {
        $this->cache = [];
        $this->cacheExpiration = [];
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function prune(): void
    {
        foreach ($this->cacheExpiration as $key => $time) {
            if (time() >= $time) {
                $this->delete($key);
            }
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function close(): void
    {
        parent::close();

        $this->clear();
    }
}
