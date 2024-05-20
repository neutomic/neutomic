<?php

declare(strict_types=1);

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
