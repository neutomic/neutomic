<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Server;

use Neu\Component\Broadcast\Address\TcpAddress;
use Neu\Component\Broadcast\Address\UnixAddress;
use Neu\Component\Broadcast\Server\Exception\ServerStateConflictException;

interface ServerInterface
{
    public function start(string $address): void;

    /**
     * @throws ServerStateConflictException
     */
    public function stop(): void;
}
