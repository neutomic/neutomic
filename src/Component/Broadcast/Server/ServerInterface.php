<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Server;

use Neu\Component\Broadcast\Address\TcpAddress;
use Neu\Component\Broadcast\Address\UnixAddress;

interface ServerInterface
{
    public function start(UnixAddress|TcpAddress $address): void;
    public function stop(): void;
}
