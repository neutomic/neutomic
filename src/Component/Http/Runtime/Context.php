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

namespace Neu\Component\Http\Runtime;

use Amp\Socket\TlsInfo;
use Closure;
use Neu\Component\Http\Exception\InvalidArgumentException;
use Neu\Component\Http\Message\ResponseInterface;

final readonly class Context
{
    /**
     * The worker ID of the context or null if the context is not worker-specific.
     */
    private null|int $workerId;

    /**
     * The client ID of the context.
     */
    private int $clientId;

    /**
     * The remote address of the client.
     *
     * @var non-empty-string
     */
    private string $remoteAddress;

    /**
     * The local address of the server.
     *
     * @var non-empty-string
     */
    private string $localAddress;

    /**
     * The TLS information of the client or null if the client is not encrypted.
     */
    private null|TlsInfo $tlsInformation;

    /**
     * The function to send an early informational response.
     *
     * @var Closure(ResponseInterface): void
     */
    private Closure $sendInformationalResponse;

    /**
     * Create a new context instance.
     *
     * @param int|null $workerId The worker ID of the context or null if the context is not worker-specific.
     * @param int $clientId The client ID of the context.
     * @param non-empty-string $remoteAddress The remote address of the client.
     * @param non-empty-string $localAddress The local address of the server.
     * @param TlsInfo|null $tlsInformation The TLS information of the client or null if the client is not encrypted.
     * @param (Closure(ResponseInterface): void) $sendInformationalResponse The function to send an early informational response.
     */
    public function __construct(null|int $workerId, int $clientId, string $remoteAddress, string $localAddress, null|TlsInfo $tlsInformation, Closure $sendInformationalResponse)
    {
        $this->workerId = $workerId;
        $this->clientId = $clientId;
        $this->remoteAddress = $remoteAddress;
        $this->localAddress = $localAddress;
        $this->tlsInformation = $tlsInformation;
        $this->sendInformationalResponse = $sendInformationalResponse;
    }

    /**
     * Get the worker ID of the context.
     *
     * @return int|null The worker ID of the context or null if the context is not worker-specific.
     */
    public function getWorkerId(): null|int
    {
        return $this->workerId;
    }

    /**
     * Get the client ID of the context.
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * Get the remote address of the client.
     *
     * @return non-empty-string The remote address of the client.
     */
    public function getRemoteAddress(): string
    {
        return $this->remoteAddress;
    }

    /**
     * Get the local address of the server.
     *
     * @return non-empty-string The local address of the server.
     */
    public function getLocalAddress(): string
    {
        return $this->localAddress;
    }

    /**
     * Get the TLS information of the client.
     *
     * @return TlsInfo|null The TLS information of the client or null if the client is not encrypted.
     */
    public function getTlsInformation(): null|TlsInfo
    {
        return $this->tlsInformation;
    }

    /**
     * Send an early informational response.
     *
     * @param ResponseInterface $response The response to send.
     *
     * @throws InvalidArgumentException If the response is not informational.
     */
    public function sendInformationalResponse(ResponseInterface $response): void
    {
        $status = $response->getStatusCode();
        if ($status >= 200) {
            throw new InvalidArgumentException('The response must be informational.');
        }

        $this->sendInformationalResponse->__invoke($response);
    }
}
