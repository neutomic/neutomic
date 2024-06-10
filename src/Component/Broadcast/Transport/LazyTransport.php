<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Transport;

use Amp\Pipeline\ConcurrentIterator;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

final class LazyTransport implements TransportInterface
{
    private TransportInterface|null $transport = null;

    /**
     * @param FactoryInterface<TransportInterface> $transportFactory
     */
    public function __construct(
        private ContainerInterface $container,
        private FactoryInterface $transportFactory,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function send(string $channel, mixed $message): void
    {
        $this->getTransportInstance()->send($channel, $message);
    }

    /**
     * @inheritDoc
     */
    public function isListening(string $channel): bool
    {
        $this->getTransportInstance()->isListening($channel);
    }

    /**
     * @inheritDoc
     */
    public function listen(string $channel): ConcurrentIterator
    {
        return $this->getTransportInstance()->listen($channel);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->getTransportInstance()->close();
    }

    /**
     * @inheritDoc
     */
    public function isClosed(): bool
    {
        return $this->getTransportInstance()->isClosed();
    }

    private function getTransportInstance(): TransportInterface
    {
        return $this->transport ??= $this->transportFactory->__invoke($this->container);
    }

}
