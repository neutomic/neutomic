<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Address;

interface SocketAddressInterface
{
    /**
     * @return non-empty-string
     */
    public function toString(): string;
}
