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

use Neu\Component\Cache\Exception\UnavailableItemException;
use Neu\Component\Cache\StoreInterface;
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
        $id = $session->getId();
        if (null === $id || $session->isRegenerated() || $session->hasChanges()) {
            $id = $this->generateIdentifier();
        }

        /** @psalm-suppress MissingThrowsDocblock */
        $this->store->update($id, static fn (): array => $session->all(), $ttl);

        return $id;
    }

    /**
     * @inheritDoc
     */
    public function read(string $id): SessionInterface
    {
        /**
         * @var array<non-empty-string, mixed> $data
         *
         * @psalm-suppress MissingThrowsDocblock
         */
        $data = $this->store->compute($id, static fn (): array => []);

        return new Session($data, $id);
    }

    /**
     * @inheritDoc
     */
    public function flush(string $id): void
    {
        /** @psalm-suppress MissingThrowsDocblock */
        $this->store->delete($id);
    }

    /**
     * @return non-empty-string
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
            /**
             * @psalm-suppress MissingThrowsDocblock
             *
             * @var non-empty-string $id
             */
            $id = SecureRandom\string(24);
        } while (!$does_not_exist($id));

        return $id;
    }
}
