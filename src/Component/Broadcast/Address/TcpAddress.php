<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Address;

readonly final class TcpAddress implements SocketAddressInterface
{
    /**
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     */
    public function __construct(public string $host, public int $port)
    {
    }

    public function toString(): string
    {
        return $this->host . ':' . $this->port;
    }
}
