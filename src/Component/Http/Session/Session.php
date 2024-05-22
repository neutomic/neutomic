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

namespace Neu\Component\Http\Session;

use Closure;
use Neu\Component\Http\Session\Exception\UnavailableItemException;

use function array_key_exists;

final class Session implements SessionInterface
{
    public const string SESSION_AGE_KEY = '__INTERNAL_SESSION_AGE__';

    /**
     * The session data.
     *
     * @var array<non-empty-string, mixed>
     */
    private array $data;

    /**
     * The session id.
     *
     * @var non-empty-string|null
     */
    private null|string $id;

    /**
     * Indicates if the session has been regenerated.
     */
    private bool $isRegenerated = false;

    /**
     * The original session data, before any changes.
     *
     * @var array<non-empty-string, mixed>
     */
    private readonly array $originalData;

    /**
     * Lifetime of the session cookie.
     *
     * @var int<0, max>
     */
    private int $age = 0;

    /**
     * Indicates if the session has been flushed.
     */
    private bool $flushed = false;

    /**
     * @param array<non-empty-string, mixed> $data
     * @param non-empty-string|null $id
     */
    public function __construct(array $data, null|string $id = null)
    {
        $this->data = $data;
        $this->originalData = $data;
        $this->id = $id;

        if (isset($data[static::SESSION_AGE_KEY])) {
            $age = (int)$data[static::SESSION_AGE_KEY];
            if ($age > 0) {
                $this->age = $age;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getId(): null|string
    {
        return $this->id;
    }

    /**
     * Compute a value, store it in the session, and return it.
     *
     * If the key already exists, the value will be returned as-is.
     *
     * If the key does not exist, the value will be computed and stored.
     *
     * @template T
     *
     * @param non-empty-string $key The key to compute the value for.
     * @param (Closure(): T) $computer The function to compute the value.
     *
     * @return T
     */
    public function compute(string $key, Closure $computer): mixed
    {
        if (!$this->has($key)) {
            $this->set($key, $computer());
        }

        /**
         * @psalm-suppress MissingThrowsDocblock
         *
         * @var T
         */
        return $this->get($key);
    }

    /**
     * Update a value in the session.
     *
     * The updater function will receive the current value as an argument.
     *
     * If the key does not exist, the updater will receive null.
     *
     * @template T
     *
     * @param non-empty-string $key
     * @param (Closure(null|T): T) $updater
     *
     * @return T
     */
    public function update(string $key, Closure $updater): mixed
    {
        /** @var T|null */
        $previous = $this->has($key) ? $this->get($key) : null;

        $this->set($key, $updater($previous));

        /**
         * @psalm-suppress MissingThrowsDocblock
         *
         * @var T
         */
        return $this->get($key);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function add(string $key, mixed $value): static
    {
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        if (!array_key_exists($key, $this->data)) {
            throw UnavailableItemException::for($key);
        }

        return $this->data[$key];
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): static
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function clear(): static
    {
        $this->data = [];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flush(): static
    {
        $this->data = [];
        $this->flushed = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasChanges(): bool
    {
        if ($this->isRegenerated()) {
            return true;
        }

        return $this->data !== $this->originalData;
    }

    /**
     * @inheritDoc
     */
    public function isRegenerated(): bool
    {
        return $this->isRegenerated;
    }

    /**
     * @inheritDoc
     */
    public function isFlushed(): bool
    {
        return $this->flushed;
    }

    /**
     * @inheritDoc
     */
    public function regenerate(): static
    {
        $session = clone $this;
        $session->isRegenerated = true;

        return $session;
    }

    /**
     * @inheritDoc
     */
    public function expireAfter(int $duration): static
    {
        $this->set(static::SESSION_AGE_KEY, $duration);
        $this->age = $duration;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function age(): int
    {
        return $this->age;
    }
}
