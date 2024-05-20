<?php

declare(strict_types=1);

namespace Neu\Component\Http\Session;

use Closure;
use Neu\Component\Http\Session\Exception\UnavailableItemException;

interface SessionInterface
{
    /**
     * Retrieve the session identifier.
     */
    public function getId(): string;

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
    public function compute(string $key, Closure $computer): mixed;

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
    public function update(string $key, Closure $updater): mixed;

    /**
     * Whether the container has the given key.
     *
     * @param non-empty-string $key
     */
    public function has(string $key): bool;

    /**
     * Set a value in the session.
     *
     * The value MUST be serializable in any format; we recommend ensuring the
     * value is JSON serializable for greatest portability.
     *
     * @param non-empty-string $key
     */
    public function set(string $key, mixed $value): static;

    /**
     * Store a value in the session if the key does not exist.
     *
     * The value MUST be serializable in any format; we recommend ensuring the
     * value is JSON serializable for greatest portability.
     *
     * @param non-empty-string $key
     */
    public function add(string $key, mixed $value): static;

    /**
     * Gets a value associated with the given key.
     *
     * @param non-empty-string $key
     *
     * @throws UnavailableItemException If the key is not present.
     */
    public function get(string $key): mixed;

    /**
     * Delete an entry from the session.
     *
     * @param non-empty-string $key
     */
    public function delete(string $key): static;

    /**
     * Retrieve all session items.
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * Clear all values.
     */
    public function clear(): static;

    /**
     * Deletes the current session data from the session and
     * deletes the session cookie. This is used if you want to ensure
     * that the previous session data can't be accessed again from the
     * user's browser.
     */
    public function flush(): static;

    /**
     * Does the session contain changes? If not, the middleware handling
     * session persistence may not need to do more work.
     */
    public function hasChanges(): bool;

    /**
     * Method to determine if the session was flushed.
     */
    public function isFlushed(): bool;

    /**
     * Regenerate the session.
     *
     * This can be done to prevent session fixation. When executed, it SHOULD
     * return a new instance; that instance should always return true for
     * isRegenerated().
     */
    public function regenerate(): static;

    /**
     * Method to determine if the session was regenerated; should return
     * true if the instance was produced via regenerate().
     */
    public function isRegenerated(): bool;

    /**
     * Sets the expiration time for the session.
     *
     * The session will expire after that many seconds
     * of inactivity.
     *
     * for example, calling:
     *
     * <code>
     *     $session->expireAfter(300);
     * </code>
     *
     * would make the session expire in 5 minutes of inactivity.
     *
     * @param int<0, max> $duration The session expiration time in seconds, 0 indicates the cookie
     *                              should be treated as a session cookie, and expire whe the window is closed.
     */
    public function expireAfter(int $duration): static;

    /**
     * Determine how long the session cookie should live.
     *
     * Generally, this will return the value provided to {@see expireAfter()}.
     *
     * If that method has not been called, the value can return one of the
     * following:
     *
     * - 0, to indicate the cookie should be treated as a
     *   session cookie, and expire when the window is closed. This should be
     *   the default behavior.
     * - If {@see expireAfter()} was provided during session creation or anytime later,
     *   the persistence engine should pull the TTL value from the session itself
     *   and return it here.
     */
    public function age(): int;
}
