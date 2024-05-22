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
