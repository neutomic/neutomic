<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Address;

readonly final class UnixAddress implements SocketAddressInterface
{
    /**
     * @param non-empty-string $socket
     */
    public function __construct(public string $socket)
    {
    }

    public function toString(): string
    {
        return $this->socket;
    }
}
