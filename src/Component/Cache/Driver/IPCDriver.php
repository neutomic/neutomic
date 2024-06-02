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

namespace Neu\Component\Cache\Driver;

use Amp\Cluster\Cluster;
use Amp\Sync\Channel;
use Neu\Component\Cache\Driver\IPCDriver\MessageInterface;
use Neu\Component\Cache\Exception\InvalidKeyException;
use Neu\Component\Cache\Exception\InvalidValueException;
use Neu\Component\Cache\Exception\RuntimeException;
use Psl\Collection\MutableVector;
use Psl\Collection\MutableVectorInterface;
use Psl\Exception\InvariantViolationException;
use Psr\Log\LoggerInterface;

use function Amp\async;
use function Psl\invariant;
use function get_class;
use function gettype;
use function is_object;

final class IPCDriver implements DriverInterface
{
    private Channel $channel;

    /**
     * @var MutableVectorInterface<string>
     */
    private $messageIdToIgnore;

    /**
     * @throws \Psl\Exception\InvariantViolationException
     */
    public function __construct(
        private LocalDriver $localDriver,
        private LoggerInterface $logger
    ) {
        invariant(Cluster::isWorker(), 'This class must be instanced only in a worker');

        $this->channel = Cluster::getChannel();

        $this->messageIdToIgnore = new MutableVector([]);

        async(function () {
            /** @var Channel<MessageInterface, MessageInterface> $message */
            while ($message = $this->channel->receive()) {
                $this->handleMessage($message);
            }
        });
    }

    public function get(string $key): mixed
    {
        return $this->localDriver->get($key);
    }

    /**
     * @throws \Amp\Serialization\SerializationException
     * @throws \Amp\Sync\ChannelException
     * @throws RuntimeException
     * @throws InvalidKeyException
     * @throws InvalidValueException
     */
    public function set(string $key, mixed $value, null|int $ttl = null): void
    {
        $message = new IPCDriver\SetMessage($key, $value, $ttl);

        $this->handleSet($message);
        $this->sendMessage($message);
    }

    /**
     * @throws \Amp\Serialization\SerializationException
     * @throws \Amp\Sync\ChannelException
     * @throws RuntimeException
     * @throws InvalidKeyException
     */
    public function delete(string $key): void
    {
        $message = new IPCDriver\DeleteMessage($key);

        $this->handleDelete($message);
        $this->sendMessage($message);
    }


    /**
     * @throws \Amp\Serialization\SerializationException
     * @throws \Amp\Sync\ChannelException
     * @throws RuntimeException
     */
    public function clear(): void
    {
        $message = new IPCDriver\ClearMessage();

        $this->handleClear($message);
        $this->sendMessage($message);
    }

    public function prune(): void
    {
        // LocalDriver automatically prunes expired keys.
    }

    /**
     * @throws \Amp\Serialization\SerializationException
     * @throws \Amp\Sync\ChannelException
     * @throws RuntimeException
     */
    public function close(): void
    {
        $message = new IPCDriver\CloseMessage();

        $this->handleClose($message);
        $this->sendMessage($message);
    }

    /**
     * @throws \Amp\Serialization\SerializationException
     * @throws \Amp\Sync\ChannelException
     */
    private function sendMessage(IPCDriver\MessageInterface $message): void
    {
        $this->messageIdToIgnore->add($message->getMessageId());
        $this->channel->send($message);
    }

    /**
     * @throws InvalidKeyException
     * @throws InvalidValueException
     * @throws RuntimeException
     * @throws InvariantViolationException
     */
    public function handleMessage(mixed $message): void
    {
        invariant(
            $message instanceof IPCDriver\MessageInterface,
            'Expected $message to be instance of %s, %s given',
            IPCDriver\MessageInterface::class,
            is_object($message) ? get_class($message) : gettype($message)
        );

        if (null !== $key = $this->messageIdToIgnore->linearSearch($message->getMessageId())) {
            $this->logger->warning('Message already handled by this worker, ignoring', [
                'workerId' => Cluster::getContextId(),
                'messageId' => $message->getMessageId(),
            ]);
            $this->messageIdToIgnore->remove($key);
            return;
        }

        if ($message instanceof IPCDriver\SetMessage) {
            $this->logger->warning('Received SetMessage', ['message' => $message]);
            $this->handleSet($message);
            return;
        }

        if ($message instanceof IPCDriver\DeleteMessage) {
            $this->logger->warning('Received DeleteMessage', ['message' => $message]);
            $this->handleDelete($message);
            return;
        }

        if ($message instanceof IPCDriver\ClearMessage) {
            $this->logger->warning('Received ClearMessage', ['message' => $message]);
            $this->handleClear($message);
            return;
        }

        if ($message instanceof IPCDriver\CloseMessage) {
            $this->logger->warning('Received CloseMessage', ['message' => $message]);
            $this->handleClose($message);
            return;
        }

        throw new RuntimeException(sprintf('Unhandled class message %s', get_class($message)));
    }

    /**
     * @throws InvalidKeyException
     * @throws InvalidValueException
     * @throws RuntimeException
     */
    private function handleSet(IPCDriver\SetMessage $message): void
    {
        $this->localDriver->set($message->key, $message->value, $message->ttl);
    }

    /**
     * @throws InvalidKeyException
     * @throws RuntimeException
     */
    private function handleDelete(IPCDriver\DeleteMessage $message): void
    {
        $this->localDriver->delete($message->key);
    }

    /**
     * @throws RuntimeException
     */
    private function handleClear(IPCDriver\ClearMessage $message): void
    {
        $this->localDriver->clear();
    }

    /**
     * @throws RuntimeException
     */
    private function handleClose(IPCDriver\CloseMessage $message): void
    {
        $this->localDriver->close();
    }
}
