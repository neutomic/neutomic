<?php

declare(strict_types=1);

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
     * @var non-empty-array<ServerSocketConfiguration>
     */
    private array $serverSocketConfigurations;
    private int $connectionLimit;
    private int $connectionLimitPerIP;
    private int $streamTimeout;
    private int $connectionTimeout;
    private int $headerSizeLimit;
    private int $bodySizeLimit;
    private int $tlsHandshakeTimeout;
    private string $logger;

    /**
     * Create a new {@see ServerInfrastructureFactory} instance.
     *
     * @param non-empty-list<ServerSocketConfiguration>|null $serverSocketConfigurations
     * @param int|null $connectionLimit
     * @param int|null $connectionLimitPerIP
     * @param int|null $streamTimeout
     * @param int|null $connectionTimeout
     * @param int|null $headerSizeLimit
     * @param int|null $bodySizeLimit
     * @param int|null $tlsHandshakeTimeout
     * @param string|null $logger
     */
    public function __construct(
        ?array $serverSocketConfigurations = null,
        ?int $connectionLimit = null,
        ?int $connectionLimitPerIP = null,
        ?int $streamTimeout = null,
        ?int $connectionTimeout = null,
        ?int $headerSizeLimit = null,
        ?int $bodySizeLimit = null,
        ?int $tlsHandshakeTimeout = null,
        ?string $logger = null,
    ) {
        $this->serverSocketConfigurations = $serverSocketConfigurations ?? ServerInfrastructure::DEFAULT_SERVER_SOCKET_CONFIGURATIONS;
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
            $container->getProject()->debug,
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
