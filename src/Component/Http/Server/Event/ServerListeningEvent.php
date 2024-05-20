<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server\Event;

use Amp\Socket\BindContext;
use Amp\Socket\SocketAddress;

/**
 * Event emitted when a server starts listening for incoming connections.
 */
final readonly class ServerListeningEvent
{
    /**
     * The socket address that the server is listening on.
     *
     * @param SocketAddress $address
     */
    public SocketAddress $address;

    /**
     * The bind context that the server is listening on.
     *
     * @param BindContext $bindContext
     */
    public BindContext $bindContext;

    public function __construct(SocketAddress $address, BindContext $bindContext)
    {
        $this->address = $address;
        $this->bindContext = $bindContext;
    }
}
