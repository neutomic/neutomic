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

use Neu\Component\Cache\Exception\ExceptionInterface as CacheException;
use Psl\SecureRandom\Exception\ExceptionInterface as SecureRandomException;
use Neu\Component\Cache\Exception\UnavailableItemException;
use Neu\Component\Cache\StoreInterface;
use Neu\Component\Http\Session\Exception\RuntimeException;
use Neu\Component\Http\Session\Session;
use Neu\Component\Http\Session\SessionInterface;
use Psl\SecureRandom;

final readonly class Storage implements StorageInterface
{
    private StoreInterface $store;

    public function __construct(StoreInterface $store)
    {
        $this->store = $store;
    }

    /**
     * @inheritDoc
     */
    public function write(SessionInterface $session, null|int $ttl = null): string
    {
        try {
            $id = $session->getId();
            if (null === $id || $session->isRegenerated() || $session->hasChanges()) {
                $id = $this->generateIdentifier();
            }

            $this->store->update($id, static fn (): array => $session->all(), $ttl);
        } catch (CacheException | SecureRandomException $e) {
            throw new RuntimeException('An error occurred while writing the session.', previous: $e);
        }

        return $id;
    }

    /**
     * @inheritDoc
     */
    public function read(string $id): SessionInterface
    {
        try {
            /** @var array<non-empty-string, mixed> $data */
            $data = $this->store->compute($id, static fn (): array => []);
        } catch (CacheException $e) {
            throw new RuntimeException('An error occurred while reading the session.', previous: $e);
        }

        return new Session($data, $id);
    }

    /**
     * @inheritDoc
     */
    public function flush(string $id): void
    {
        try {
            $this->store->delete($id);
        } catch (CacheException $e) {
            throw new RuntimeException('An error occurred while flushing the session.', previous: $e);
        }
    }

    /**
     * Generate a new session identifier.
     *
     * @throws CacheException If an error occurs while generating the identifier.
     * @throws SecureRandomException If an error occurs while generating the identifier.
     *
     * @return non-empty-string The generated identifier.
     */
    private function generateIdentifier(): string
    {
        $does_not_exist =
            /**
             * @param non-empty-string $id
             */
            function (string $id): bool {
                try {
                    $this->store->get($id);
                } catch (UnavailableItemException) {
                    return true;
                }

                return false;
            };

        do {
            /** @var non-empty-string $id */
            $id = SecureRandom\string(24);
        } while (!$does_not_exist($id));

        return $id;
    }
}
