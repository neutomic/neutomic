<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server;

use Amp\Cluster\Cluster;
use Amp\Http\Server\Driver\ClientFactory;
use Amp\Http\Server\Driver\ConnectionLimitingClientFactory;
use Amp\Http\Server\Driver\ConnectionLimitingServerSocketFactory;
use Amp\Http\Server\Driver\DefaultHttpDriverFactory;
use Amp\Http\Server\Driver\HttpDriverFactory;
use Amp\Http\Server\Driver\SocketClientFactory;
use Amp\Socket\BindContext;
use Amp\Socket\Certificate;
use Amp\Socket\InternetAddress;
use Amp\Socket\ServerSocket;
use Amp\Socket\ServerTlsContext;
use Amp\Sync\LocalSemaphore;
use Generator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @psalm-type ServerSocketTlsBindConfiguration = array{
 *     minimum-version?: int,
 *     verify-peer?: bool,
 *     capture-peer?: bool,
 *     verify-depth?: int,
 *     security-level?: 0|1|2|3|4|5,
 *     peer-name?: non-empty-string,
 *     ciphers?: non-empty-string,
 *     alpn-protocols?: non-empty-list<non-empty-string>,
 *     certificate-authority?: array{
 *         file?: non-empty-string,
 *         path?: non-empty-string,
 *     },
 *     certificate?: array{
 *         file: non-empty-string,
 *         key?: non-empty-string,
 *         passphrase?: non-empty-string,
 *     },
 *     certificates?: non-empty-array<string, array{
 *         file: non-empty-string,
 *         key?: non-empty-string,
 *         passphrase?: non-empty-string,
 *     }>,
 * }
 * @psalm-type ServerSocketBindConfiguration = array{
 *     tcp-no-delay?: bool,
 *     reuse-port?: bool,
 *     broadcast?: bool,
 *     tls?: ServerSocketTlsBindConfiguration,
 * }
 * @psalm-type ServerSocketConfiguration = array{
 *     host: non-empty-string,
 *     port: int,
 *     bind?: ServerSocketBindConfiguration,
 * }
 */
final readonly class ServerInfrastructure
{
    /**
     * Default server socket configuration.
     */
    public const array DEFAULT_SERVER_SOCKET_CONFIGURATIONS = [
        [
            'host' => '0.0.0.0',
            'port' => 0,
        ]
    ];

    /**
     * Default number of connections allowed at any given time.
     */
    public const int DEFAULT_CONNECTION_LIMIT = 1000;

    /**
     * Default number of connections allowed per IP address.
     */
    public const int DEFAULT_CONNECTION_LIMIT_PER_IP = 100;

    /**
     * Default maximum duration in milliseconds that a stream can remain open.
     */
    public const int DEFAULT_STREAM_TIMEOUT = 1000;

    /**
     * Default timeout in milliseconds for establishing a connection.
     */
    public const int DEFAULT_CONNECTION_TIMEOUT = 1000;

    /**
     * Default maximum size in bytes of a request header.
     */
    public const int DEFAULT_HEADER_SIZE_LIMIT = 1000;

    /**
     * Default maximum size in bytes of a request body.
     */
    public const int DEFAULT_BODY_SIZE_LIMIT = 1000;

    /**
     * The default timeout in milliseconds for the TLS handshake.
     */
    public const int DEFAULT_TLS_HANDSHAKE_TIMEOUT = 5000;

    /**
     * Whether to enable debug mode.
     */
    private bool $debug;

    /**
     * @var non-empty-list<ServerSocketConfiguration> The socket configurations.
     */
    private array $serverSocketConfigurations;

    /**
     * The connections limit.
     */
    private int $connectionLimit;

    /**
     * The connection limit per IP.
     */
    private int $connectionLimitPerIP;

    /**
     * The stream timeout.
     */
    private int $streamTimeout;

    /**
     * The connection timeout.
     */
    private int $connectionTimeout;

    /**
     * The header size limit.
     */
    private int $headerSizeLimit;

    /**
     * The body size limit.
     */
    private int $bodySizeLimit;

    /**
     * The TLS handshake timeout.
     */
    private int $tlsHandshakeTimeout;

    /**
     * The logger.
     */
    private LoggerInterface $logger;

    /**
     * Create a new instance of {@see ServerInfrastructure}.
     *
     * @param bool $debug Whether to enable debug mode.
     * @param non-empty-list<ServerSocketConfiguration> $serverSocketConfigurations The socket configurations.
     * @param int $connectionLimit The connection limit.
     * @param int $connectionLimitPerIP The connection limit per IP.
     * @param int $streamTimeout The stream timeout.
     * @param int $connectionTimeout The connection timeout.
     * @param int $headerSizeLimit The header size limit.
     * @param int $bodySizeLimit The body size limit.
     * @param bool $http2 Whether to enable HTTP/2.
     * @param bool $http2Upgrade Whether to upgrade to HTTP/2.
     * @param bool $push Whether to enable server push.
     * @param int $tlsHandshakeTimeout The TLS handshake timeout.
     * @param LoggerInterface $logger The logger.
     */
    public function __construct(
        bool $debug,
        array $serverSocketConfigurations = self::DEFAULT_SERVER_SOCKET_CONFIGURATIONS,
        int $connectionLimit = self::DEFAULT_CONNECTION_LIMIT,
        int $connectionLimitPerIP = self::DEFAULT_CONNECTION_LIMIT_PER_IP,
        int $streamTimeout = self::DEFAULT_STREAM_TIMEOUT,
        int $connectionTimeout = self::DEFAULT_CONNECTION_TIMEOUT,
        int $headerSizeLimit = self::DEFAULT_HEADER_SIZE_LIMIT,
        int $bodySizeLimit = self::DEFAULT_BODY_SIZE_LIMIT,
        int $tlsHandshakeTimeout = self::DEFAULT_TLS_HANDSHAKE_TIMEOUT,
        LoggerInterface $logger = new NullLogger(),
    ) {
        $this->debug = $debug;
        $this->serverSocketConfigurations = $serverSocketConfigurations;
        $this->connectionLimit = $connectionLimit;
        $this->connectionLimitPerIP = $connectionLimitPerIP;
        $this->streamTimeout = $streamTimeout;
        $this->connectionTimeout = $connectionTimeout;
        $this->headerSizeLimit = $headerSizeLimit;
        $this->bodySizeLimit = $bodySizeLimit;
        $this->tlsHandshakeTimeout = $tlsHandshakeTimeout;
        $this->logger = $logger;
    }

    /**
     * Create a client factory.
     *
     * @return ClientFactory The client factory.
     */
    public function createClientFactory(): ClientFactory
    {
        return new ConnectionLimitingClientFactory(
            new SocketClientFactory($this->logger, $this->tlsHandshakeTimeout),
            $this->logger,
            $this->connectionLimitPerIP,
        );
    }

    /**
     * Create server sockets that the server will listen on.
     *
     * @return Generator<null, ServerSocket, null, HttpDriverFactory> Yielded server sockets, and returns the HTTP driver factory.
     */
    public function createServerSockets(): Generator
    {
        $serverSocketConfigurations = $this->serverSocketConfigurations;
        if ([] === $serverSocketConfigurations) {
            $serverSocketConfigurations = self::DEFAULT_SERVER_SOCKET_CONFIGURATIONS;
        }

        $httpDriverFactory = new DefaultHttpDriverFactory(
            // disable Amp logs when debug is disabled
            $this->debug ? $this->logger : new NullLogger(),
            $this->streamTimeout,
            $this->connectionTimeout,
            $this->headerSizeLimit,
            $this->bodySizeLimit,
            http2Enabled: true,
            allowHttp2Upgrade: true,
            pushEnabled: true,
        );

        $serverFactory = new ConnectionLimitingServerSocketFactory(
            new LocalSemaphore($this->connectionLimit),
            Cluster::getServerSocketFactory(),
        );

        foreach ($serverSocketConfigurations as $socketConfiguration) {
            $address = new InternetAddress($socketConfiguration['host'], $socketConfiguration['port']);
            $bindContext = $this->createBindContext($socketConfiguration['bind'] ?? null);

            $tlsContext = $bindContext?->getTlsContext()?->withApplicationLayerProtocols(
                $httpDriverFactory->getApplicationLayerProtocols(),
            );

            yield $serverFactory->listen($address, $bindContext?->withTlsContext($tlsContext));
        }

        return $httpDriverFactory;
    }

    /**
     * Create a bind context from the configuration.
     *
     * @param ServerSocketBindConfiguration|null $bindConfiguration The bind configuration.
     *
     * @return BindContext The bind context.
     */
    private function createBindContext(?array $bindConfiguration): BindContext
    {
        $context = new BindContext();
        $tcpNoDelay = $bindConfiguration['tcp-no-delay'] ?? false;
        if ($tcpNoDelay) {
            $context = $context->withTcpNoDelay();
        } else {
            $context = $context->withoutTcpNoDelay();
        }

        $broadcast = $bindConfiguration['broadcast'] ?? false;
        if ($broadcast) {
            $context = $context->withBroadcast();
        } else {
            $context = $context->withoutBroadcast();
        }

        $reusePort = $bindConfiguration['reuse-port'] ?? false;
        if ($reusePort) {
            $context = $context->withReusePort();
        } else {
            $context = $context->withoutReusePort();
        }

        $tlsConfiguration = $bindConfiguration['tls'] ?? null;
        if (null !== $tlsConfiguration) {
            $tls = new ServerTlsContext();
            $minimumVersion = $tlsConfiguration['minimum-version'] ?? null;
            if (null !== $minimumVersion) {
                $tls = $tls->withMinimumVersion($minimumVersion);
            }

            $peerName = $tlsConfiguration['peer-name'] ?? null;
            if (null !== $peerName) {
                $tls = $tls->withPeerName($peerName);
            }

            $verifyDepth = $tlsConfiguration['verify-depth'] ?? null;
            if (null !== $verifyDepth) {
                $tls = $tls->withVerificationDepth($verifyDepth);
            }

            $ciphers = $tlsConfiguration['ciphers'] ?? null;
            if (null !== $ciphers) {
                $tls = $tls->withCiphers($ciphers);
            }

            $caPath = $tlsConfiguration['certificate-authority']['path'] ?? null;
            if (null !== $caPath) {
                $tls = $tls->withCaPath($caPath);
            }

            $caFile = $tlsConfiguration['certificate-authority']['file'] ?? null;
            if (null !== $caFile) {
                $tls = $tls->withCaFile($caFile);
            }

            $securityLevel = $tlsConfiguration['security-level'] ?? null;
            if (null !== $securityLevel) {
                $tls = $tls->withSecurityLevel($securityLevel);
            }

            $alpnProtocols = $tlsConfiguration['alpn-protocols'] ?? null;
            if (null !== $alpnProtocols) {
                $tls = $tls->withApplicationLayerProtocols($alpnProtocols);
            }

            $verifyPeer = $tlsConfiguration['verify-peer'] ?? true;
            if ($verifyPeer) {
                $tls = $tls->withPeerVerification();
            } else {
                $tls = $tls->withoutPeerVerification();
            }

            $capturePeer = $tlsConfiguration['capture-peer'] ?? false;
            if ($capturePeer) {
                $tls = $tls->withPeerCapturing();
            } else {
                $tls = $tls->withoutPeerCapturing();
            }

            $certificate = $tlsConfiguration['certificate'] ?? null;
            if (null !== $certificate) {
                $tls = $tls->withDefaultCertificate(new Certificate(
                    $certificate['file'],
                    $certificate['key'] ?? null,
                    $certificate['passphrase'] ?? null,
                ));
            }

            $certificates = $tlsConfiguration['certificates'] ?? [];
            foreach ($certificates as $domain => $certificate) {
                $certificates[$domain] = new Certificate(
                    $certificate['file'],
                    $certificate['key'] ?? null,
                    $certificate['passphrase'] ?? null,
                );
            }

            $tls = $tls->withCertificates($certificates);

            $context = $context->withTlsContext($tls);
        }

        return $context;
    }
}
