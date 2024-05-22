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

namespace Neu\Component\Http\Session\Configuration;

use Neu\Component\Cache\StoreInterface;

final readonly class StorageConfiguration
{
    /**
     * The cache store service identifier.
     *
     * @param non-empty-string $store
     */
    public string $store;

    /**
     * Creates a new {@see StorageConfiguration} instance.
     *
     * @param non-empty-string $store The cache store service identifier.
     */
    public function __construct(string $store = StoreInterface::class)
    {
        $this->store = $store;
    }

    /**
     * Returns a new {@see StorageConfiguration} instance with the specified store configuration.
     *
     * @param non-empty-string $store The cache store service identifier.
     */
    public function withStore(string $store): static
    {
        return new static($store);
    }
}
