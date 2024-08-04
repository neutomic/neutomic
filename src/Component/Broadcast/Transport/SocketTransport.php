<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Transport;

use Amp\Pipeline\ConcurrentIterator;
use Amp;
use Neu\Component\Broadcast\Exception\ClosedTransportException;
use Neu\Component\Broadcast\Exception\RuntimeException;
use Neu\Component\Broadcast\Transport\Internal\ChannelTransport;

final class SocketTransport implements TransportInterface
{
    private TransportInterface $transport;

    private bool $explicitlyClosed = false;

    /**
     * @param string $address
     *
     * @throws RuntimeException if fails to connect to the server
     */
    public function __construct(private string $address)
    {
        $this->connect();
    }

    /**
     * @inheritDoc
     */
    public function send(string $channel, mixed $message): void
    {
        $this->reconnect();

        $this->transport->send($channel, $message);
    }

    /**
     * @inheritDoc
     */
    public function isListening(string $channel): bool
    {
        return $this->transport->isListening($channel);
    }

    /**
     * @inheritDoc
     */
    public function listen(string $channel): ConcurrentIterator
    {
        $this->reconnect();

        return $this->transport->listen($channel);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->explicitlyClosed = true;
        $this->transport->close();
    }

    /**
     * @inheritDoc
     */
    public function isClosed(): bool
    {
        return $this->transport->isClosed();
    }

    /**
     * This method should only be called on slf::__construct() and self::reconnect()
     *
     * @throws RuntimeException if fails to connect to the server
     */
    private function connect(): void
    {
        try {
            $connection = Amp\Socket\connect($this->address);
        } catch (Amp\CancelledException|Amp\Socket\ConnectException $e) {
            throw new RuntimeException('Failed to connect to "'.$this->address.'"', previous: $e);
        }

        $streamChannel = new Amp\ByteStream\StreamChannel($connection, $connection);

        $this->transport = new ChannelTransport($streamChannel, $streamChannel);
    }

    /**
     * Ensure that there is an active connection, if not, try to connect one more time.
     *
     * @throws RuntimeException if fails to connect to the server
     */
    private function reconnect(): void
    {
        if (!$this->transport->isClosed() || $this->explicitlyClosed) {
            return;
        }

        $this->transport->close();
        $this->connect();
    }
}
