<?php

declare(strict_types=1);

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
     * @param array<string, mixed> $data
     */
    private array $data;

    /**
     * The session id.
     */
    private string $id;

    /**
     * Indicates if the session has been regenerated.
     */
    private bool $isRegenerated = false;

    /**
     * The original session data, before any changes.
     *
     * @var array<string, mixed>
     */
    private readonly array $originalData;

    /**
     * Lifetime of the session cookie.
     */
    private int $age = 0;

    /**
     * Indicates if the session has been flushed.
     */
    private bool $flushed = false;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data, string $id = '')
    {
        $this->data = $data;
        $this->originalData = $data;
        $this->id = $id;

        if (isset($data[static::SESSION_AGE_KEY])) {
            $this->age = (int)$data[static::SESSION_AGE_KEY];
        }
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function compute(string $key, Closure $computer): mixed
    {
        if (!$this->has($key)) {
            $this->set($key, $computer());
        }

        return $this->get($key);
    }

    /**
     * @inheritDoc
     */
    public function update(string $key, Closure $updater): mixed
    {
        $this->set($key, $updater($this->has($key) ? $this->get($key) : null));

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
