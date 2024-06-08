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

namespace Neu\Component\Http\Server\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Server\ServerInfrastructure;
use Psr\Log\LoggerInterface;

/**
 * A factory for creating a {@see ServerInfrastructure} instance.
 *
 * @psalm-import-type ServerSocketConfiguration from ServerInfrastructure
 *
 * @implements FactoryInterface<ServerInfrastructure>
 */
final readonly class ServerInfrastructureFactory implements FactoryInterface
{
    /**
     * @var list<ServerSocketConfiguration>
     */
    private array $serverSocketConfigurations;

    /**
     * The maximum number of connections allowed.
     *
     * @var int
     */
    private int $connectionLimit;

    /**
     * The maximum number of connections allowed per IP address.
     *
     * @var int
     */
    private int $connectionLimitPerIP;

    /**
     * The maximum time in seconds to wait for a stream to become readable or writable.
     *
     * @var int
     */
    private int $streamTimeout;

    /**
     * The maximum time in seconds to wait for a connection to be established.
     *
     * @var int
     */
    private int $connectionTimeout;

    /**
     * The maximum size in bytes of the HTTP header.
     *
     * @var int
     */
    private int $headerSizeLimit;

    /**
     * The maximum size in bytes of the HTTP body.
     *
     * @var int
     */
    private int $bodySizeLimit;

    /**
     * The maximum time in seconds to wait for a TLS handshake to complete.
     *
     * @var int
     */
    private int $tlsHandshakeTimeout;

    /**
     * The logger service identifier.
     *
     * @var non-empty-string
     */
    private string $logger;

    /**
     * Create a new {@see ServerInfrastructureFactory} instance.
     *
     * @param list<ServerSocketConfiguration>|null $serverSocketConfigurations
     * @param int|null $connectionLimit
     * @param int|null $connectionLimitPerIP
     * @param int|null $streamTimeout
     * @param int|null $connectionTimeout
     * @param int|null $headerSizeLimit
     * @param int|null $bodySizeLimit
     * @param int|null $tlsHandshakeTimeout
     * @param non-empty-string|null $logger
     */
    public function __construct(
        null|array $serverSocketConfigurations = null,
        null|int $connectionLimit = null,
        null|int $connectionLimitPerIP = null,
        null|int $streamTimeout = null,
        null|int $connectionTimeout = null,
        null|int $headerSizeLimit = null,
        null|int $bodySizeLimit = null,
        null|int $tlsHandshakeTimeout = null,
        null|string $logger = null,
    ) {
        $this->serverSocketConfigurations = $serverSocketConfigurations ?? [];
        $this->connectionLimit = $connectionLimit ?? ServerInfrastructure::DEFAULT_CONNECTION_LIMIT;
        $this->connectionLimitPerIP = $connectionLimitPerIP ?? ServerInfrastructure::DEFAULT_CONNECTION_LIMIT_PER_IP;
        $this->streamTimeout = $streamTimeout ?? ServerInfrastructure::DEFAULT_STREAM_TIMEOUT;
        $this->connectionTimeout = $connectionTimeout ?? ServerInfrastructure::DEFAULT_CONNECTION_TIMEOUT;
        $this->headerSizeLimit = $headerSizeLimit ?? ServerInfrastructure::DEFAULT_HEADER_SIZE_LIMIT;
        $this->bodySizeLimit = $bodySizeLimit ?? ServerInfrastructure::DEFAULT_BODY_SIZE_LIMIT;
        $this->tlsHandshakeTimeout = $tlsHandshakeTimeout ?? ServerInfrastructure::DEFAULT_TLS_HANDSHAKE_TIMEOUT;
        $this->logger = $logger ?? LoggerInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): object
    {
        return new ServerInfrastructure(
            $this->serverSocketConfigurations,
            $this->connectionLimit,
            $this->connectionLimitPerIP,
            $this->streamTimeout,
            $this->connectionTimeout,
            $this->headerSizeLimit,
            $this->bodySizeLimit,
            $this->tlsHandshakeTimeout,
            $container->getTyped($this->logger, LoggerInterface::class),
        );
    }
}
