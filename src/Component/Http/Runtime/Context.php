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

use Amp\Http\Server\Driver\Client;
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
     * The client of the context.
     */
    private Client $client;

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
     * @param Client $client The client of the context.
     * @param (Closure(ResponseInterface): void) $sendInformationalResponse The function to send an early informational response.
     */
    public function __construct(null|int $workerId, Client $client, Closure $sendInformationalResponse)
    {
        $this->workerId = $workerId;
        $this->client = $client;
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
     * Get the client of the context.
     *
     * @return Client The client of the context.
     */
    public function getClient(): Client
    {
        return $this->client;
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
