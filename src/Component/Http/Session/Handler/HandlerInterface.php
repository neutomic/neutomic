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

namespace Neu\Component\Http\Session\Handler;

use Neu\Component\Http\Session\Exception\InvalidIdentifierException;
use Neu\Component\Http\Session\Exception\RuntimeException;
use Neu\Component\Http\Session\SessionInterface;

interface HandlerInterface
{
    /**
     * Save a session, and return the session identifier.
     *
     * @param SessionInterface $session The session to save.
     * @param null|positive-int $ttl The session TTL (null to use the default TTL).
     *
     * @throws RuntimeException If the session cannot be saved.
     *
     * @return non-empty-string The session identifier.
     */
    public function save(SessionInterface $session, null|int $ttl = null): string;

    /**
     * Load a session using its identifier.
     *
     * If the session is expired, or not found, a new session is returned.
     *
     * @param non-empty-string $identifier The session identifier.
     *
     * @throws InvalidIdentifierException If the session identifier is invalid.
     * @throws RuntimeException If failed to load the session.
     *
     * @return SessionInterface The session instance.
     */
    public function load(string $identifier): SessionInterface;

    /**
     * Flush a session using its identifier.
     *
     * @param non-empty-string $identifier The session identifier.
     *
     * @throws RuntimeException If the session cannot be flushed.
     */
    public function flush(string $identifier): void;
}
