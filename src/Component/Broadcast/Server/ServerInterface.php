<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Server;

use Neu\Component\Broadcast\Address\TcpAddress;
use Neu\Component\Broadcast\Address\UnixAddress;

interface ServerInterface
{
    /**
     * @param non-empty-string $address
     */
    public function start(string $address): void;
    public function stop(): void;
}
