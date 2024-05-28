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

namespace Neu\Component\Http\Session\Storage;

use Neu\Component\Http\Session\Exception\RuntimeException;
use Neu\Component\Http\Session\SessionInterface;

interface StorageInterface
{
    /**
     * Write the given session to the storage, and return its ID.
     *
     * If {@see SessionInterface::getId()} returns null, the storage should generate a new ID, and return it.
     *
     * @param null|int<1, max> $ttl
     *
     * @throws RuntimeException If an error occurs while writing the session.
     */
    public function write(SessionInterface $session, null|int $ttl = null): string;

    /**
     * Read the session with the given ID from the storage.
     *
     * If the session does not exist in the storage, this method must return a session instance with no data.
     *
     * Calling {@see SessionInterface::getId()} on the returned instance, must return the given ID.
     *
     * @param non-empty-string $id
     *
     * @throws RuntimeException If an error occurs while reading the session.
     */
    public function read(string $id): SessionInterface;

    /**
     * Flush the given session id.
     *
     * @param non-empty-string $id
     *
     * @throws RuntimeException If an error occurs while flushing the session.
     */
    public function flush(string $id): void;
}
