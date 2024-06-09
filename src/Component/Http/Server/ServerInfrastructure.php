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

namespace Neu\Component\Http\Server;

use Amp\Cluster\Cluster;
use Amp\Http\Server\Driver\ConnectionLimitingClientFactory;
use Amp\Http\Server\Driver\ConnectionLimitingServerSocketFactory;
use Amp\Http\Server\Driver\DefaultHttpDriverFactory;
use Amp\Http\Server\Driver\HttpDriver;
use Amp\Http\Server\Driver\SocketClientFactory;
use Amp\Http\Server\SocketHttpServer;
use Amp\Socket\BindContext;
use Amp\Socket\Certificate;
use Amp\Socket\InternetAddress;
use Amp\Socket\ServerTlsContext;
use Amp\Sync\LocalSemaphore;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Server infrastructure, used to create server sockets and client factories.
 *
 * @psalm-type ServerSocketTlsBindConfiguration = array{
 *     minimum-version?: int,
 *     verify-peer?: bool,
 *     capture-peer?: bool,
 *     verify-depth?: int,
 *     security-level?: 0|1|2|3|4|5,
 *     peer-name?: non-empty-string,
 *     ciphers?: non-empty-string,
 *     alpn-protocols?: list<non-empty-string>,
 *     certificate-authority?: array{
 *         file?: non-empty-string,
 *         path?: non-empty-string,
 *     },
 *     certificate?: array{
 *         file: non-empty-string,
 *         key?: non-empty-string,
 *         passphrase?: non-empty-string,
 *     },
 *     certificates?: array<string, array{
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
 *
 * @psalm-suppress MissingThrowsDocblock
 * @psalm-suppress RedundantCondition
 */
final readonly class ServerInfrastructure
{
    /**
     * Default server socket configuration.
     */
    public const array DEFAULT_SERVER_SOCKET_CONFIGURATIONS = [
        [
            'host' => '127.0.0.1',
            'port' => 8080,
        ],
        [
            'host' => '::1',
            'port' => 8080,
        ],
    ];

    /**
     * Default number of connections allowed at any given time.
     */
    public const int DEFAULT_CONNECTION_LIMIT = 1024;

    /**
     * Default number of connections allowed per IP address.
     */
    public const int DEFAULT_CONNECTION_LIMIT_PER_IP = 256;

    /**
     * Default maximum duration in seconds that a stream can remain open.
     */
    public const int DEFAULT_STREAM_TIMEOUT = HttpDriver::DEFAULT_STREAM_TIMEOUT;

    /**
     * Default timeout in milliseconds for establishing a connection.
     */
    public const int DEFAULT_CONNECTION_TIMEOUT = HttpDriver::DEFAULT_CONNECTION_TIMEOUT;

    /**
     * Default maximum size in bytes of a request header.
     */
    public const int DEFAULT_HEADER_SIZE_LIMIT = HttpDriver::DEFAULT_HEADER_SIZE_LIMIT;

    /**
     * Default maximum size in bytes of a request body.
     */
    public const int DEFAULT_BODY_SIZE_LIMIT = HttpDriver::DEFAULT_BODY_SIZE_LIMIT;

    /**
     * The default timeout in seconds for the TLS handshake.
     */
    public const int DEFAULT_TLS_HANDSHAKE_TIMEOUT = 5;

    /**
     * @var list<ServerSocketConfiguration> The socket configurations.
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
     * @param list<ServerSocketConfiguration> $serverSocketConfigurations The socket configurations.
     * @param int $connectionLimit The connection limit.
     * @param int $connectionLimitPerIP The connection limit per IP.
     * @param int $streamTimeout The stream timeout.
     * @param int $connectionTimeout The connection timeout.
     * @param int $headerSizeLimit The header size limit.
     * @param int $bodySizeLimit The body size limit.
     * @param int $tlsHandshakeTimeout The TLS handshake timeout.
     * @param LoggerInterface $logger The logger.
     */
    public function __construct(
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
     * Create a new socket HTTP server.
     *
     * @return SocketHttpServer The socket HTTP server.
     */
    public function createSocketHttpServer(): SocketHttpServer
    {
        $serverFactory = Cluster::getServerSocketFactory();
        if ($this->connectionLimit > 0) {
            $connectionLimit = $this->connectionLimit;
            assert($this->logger->debug(
                'Connection limiting enabled, limiting to ' . ((string) $connectionLimit) . ' connections.',
            ) || true);

            $serverFactory = new ConnectionLimitingServerSocketFactory(
                new LocalSemaphore($connectionLimit),
                $serverFactory,
            );
        }

        $clientFactory = new SocketClientFactory($this->logger, $this->tlsHandshakeTimeout);
        if ($this->connectionLimitPerIP > 0) {
            $connectionLimitPerIP = $this->connectionLimitPerIP;
            assert($this->logger->debug(
                'Connection limiting per IP enabled, limiting to ' . ((string) $connectionLimitPerIP) . ' connections per IP.',
            ) || true);

            $clientFactory = new ConnectionLimitingClientFactory(
                $clientFactory,
                $this->logger,
                $connectionLimitPerIP,
            );
        }

        $httpDriverFactory = new DefaultHttpDriverFactory(
            $this->logger,
            $this->streamTimeout,
            $this->connectionTimeout,
            $this->headerSizeLimit,
            $this->bodySizeLimit,
            http2Enabled: true,
            allowHttp2Upgrade: true,
            pushEnabled: true,
        );

        $server = new SocketHttpServer(
            $this->logger,
            $serverFactory,
            $clientFactory,
            allowedMethods: null,
            httpDriverFactory: $httpDriverFactory,
        );

        $serverSocketConfigurations = $this->serverSocketConfigurations;
        if ([] === $serverSocketConfigurations) {
            $serverSocketConfigurations = self::DEFAULT_SERVER_SOCKET_CONFIGURATIONS;
        }

        foreach ($serverSocketConfigurations as $socketConfiguration) {
            /** @var string $host */
            $host = $socketConfiguration['host'];
            /** @var int<0, 65535> $port */
            $port = $socketConfiguration['port'];

            $address = new InternetAddress($host, $port);
            $bindContext = $this->createBindContext($socketConfiguration['bind'] ?? null);

            $server->expose($address, $bindContext);
        }

        return $server;
    }

    /**
     * Get the server urls.
     *
     * @return list<non-empty-string> The server urls.
     */
    public function getUrls(): array
    {
        $urls = [];
        foreach ($this->serverSocketConfigurations as $socketConfiguration) {
            $scheme = ($socketConfiguration['bind']['tls'] ?? null) !== null ? 'https' : 'http';
            $host = $socketConfiguration['host'];
            $port = $socketConfiguration['port'];
            $url = $scheme . '://' . $host;
            if ('http' === $scheme && 80 !== $port) {
                $url .= ':' . ((string) $port);
            } elseif ('https' === $scheme && 443 !== $port) {
                $url .= ':' . ((string) $port);
            }

            $urls[] = $url;
        }

        return $urls;
    }

    /**
     * Create a bind context from the configuration.
     *
     * @param ServerSocketBindConfiguration|null $bindConfiguration The bind configuration.
     *
     * @return BindContext The bind context.
     */
    private function createBindContext(null|array $bindConfiguration): BindContext
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
