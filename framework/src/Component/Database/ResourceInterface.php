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

namespace Neu\Component\Database;

interface ResourceInterface
{
    /**
     * Get the last time this resource was used.
     *
     * @return int The last time this resource was used in Unix timestamps.
     */
    public function getLastUsedAt(): int;

    /**
     * Check if the resource is closed.
     */
    public function isClosed(): bool;

    /**
     * Close the resource.
     *
     * No further operations might be performed.
     */
    public function close(): void;
}
