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

namespace Neu\Framework\Middleware;

use DeflateContext;
use Generator;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Message\Body;
use Neu\Component\Http\Message\BodyInterface;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\Runtime\Middleware\PrioritizedMiddlewareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Override;

use function array_map;
use function deflate_add;
use function deflate_init;
use function explode;
use function preg_match;
use function restore_error_handler;
use function set_error_handler;
use function strlen;
use function strtolower;

use const ZLIB_ENCODING_GZIP;
use const ZLIB_ENCODING_RAW;
use const ZLIB_FINISH;
use const ZLIB_SYNC_FLUSH;

/**
 * A middleware that compresses the response body.
 *
 * @psalm-suppress RedundantCondition
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class CompressionMiddleware implements PrioritizedMiddlewareInterface
{
    /**
     * The regular expression used to match content encodings.
     */
    private const string ENCODING_REGEX = '/^(gzip|deflate)(?:;q=(1(?:\.0{1,3})?|0(?:\.\d{1,3})?))?$/i';

    /**
     * The identity encoding.
     */
    private const string IDENTITY_ENCODING = 'identity';

    /**
     * The supported encodings.
     */
    private const array SUPPORTED_ENCODINGS = [
        'gzip' => ZLIB_ENCODING_GZIP,
        'deflate' => ZLIB_ENCODING_RAW,
    ];

    /**
     * The default minimum length of compressible content.
     *
     * @see http://webmasters.stackexchange.com/questions/31750/what-is-recommended-minimum-object-size-for-deflate-performance-benefits
     */
    public const int DEFAULT_MINIMUM_COMPRESSIBLE_CONTENT_LENGTH = 860;

    /**
     * The default regular expression used to match compressible content types.
     */
    public const string DEFAULT_COMPRESSIBLE_CONTENT_TYPES_REGEX = '/^(?!(text\/event-stream))(text\/.+|application\/(?:json|(?:x-)?javascript)|[^\/]+\/[^\/]+\+xml|[^\/]+\/xml)(?:\s*;|$)/i';

    /**
     * The default level of compression.
     */
    public const int DEFAULT_LEVEL = -1;

    /**
     * The default memory level.
     */
    public const int DEFAULT_MEMORY = 8;

    /**
     * The default window size.
     */
    public const int DEFAULT_WINDOW = 15;

    /**
     * The default priority of the middleware.
     */
    public const int PRIORITY = 0;

    /**
     * The logger used to log events.
     */
    private LoggerInterface $logger;

    /**
     * The minimum length of compressible content.
     *
     * @var int<0, max>
     */
    private int $minimumCompressionContentLength;

    /**
     * The regular expression used to match compressible content types.
     *
     * @var non-empty-string
     */
    private string $compressibleContentTypesRegex;

    /**
     * The level of compression.
     *
     * @var int<-1, 9>
     */
    private int $level;

    /**
     * The memory level.
     *
     * @var int<1, 9>
     */
    private int $memory;

    /**
     * The window size.
     *
     * @var int<8, 15>
     */
    private int $window;

    /**
     * The priority of the middleware.
     */
    private int $priority;

    /**
     * Create a new {@see CompressionMiddleware} instance.
     *
     * @param LoggerInterface $logger The logger used to log events.
     * @param int<0, max> $minimumCompressionContentLength The minimum length of compressible content.
     * @param non-empty-string $compressibleContentTypesRegex The regular expression used to match compressible content types.
     * @param int<-1, 9> $level The level of compression.
     * @param int<1, 9> $memory The memory level.
     * @param int<8, 15> $window The window size.
     * @param int $priority The priority of the middleware.
     *
     * @throws RuntimeException If the zlib extension is not loaded.
     */
    public function __construct(
        LoggerInterface $logger = new NullLogger(),
        int $minimumCompressionContentLength = self::DEFAULT_MINIMUM_COMPRESSIBLE_CONTENT_LENGTH,
        string $compressibleContentTypesRegex = self::DEFAULT_COMPRESSIBLE_CONTENT_TYPES_REGEX,
        int $level = self::DEFAULT_LEVEL,
        int $memory = self::DEFAULT_MEMORY,
        int $window = self::DEFAULT_WINDOW,
        int $priority = self::PRIORITY
    ) {
        if (!extension_loaded('zlib')) {
            throw new RuntimeException('The compression middleware requires the zlib extension');
        }

        $this->logger = $logger;
        $this->minimumCompressionContentLength = $minimumCompressionContentLength;
        $this->compressibleContentTypesRegex = $compressibleContentTypesRegex;
        $this->level = $level;
        $this->memory = $memory;
        $this->window = $window;
        $this->priority = $priority;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function process(Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        $response = $next->handle($context, $request);

        $body = $response->getBody();
        if (null === $body) {
            assert($this->logger->debug('skipping compression for response with no body') || true);

            return $response;
        }

        if ($response->hasHeader('content-encoding')) {
            assert($this->logger->debug('Skipping compression for response with existing content encoding') || true);

            return $response;
        }

        $contentLength = $response->getHeaderLine('Content-Length');
        if (null !== $contentLength) {
            $contentLength = (int) $contentLength;
            if ($contentLength < $this->minimumCompressionContentLength) {
                assert(
                    $this->logger->debug('Skipping compression for response with content length {contentLength}', ['contentLength' => $contentLength]) || true
                );

                return $response;
            }
        }

        $contentTypes = $response->getHeaderLine('Content-Type');
        if (null === $contentTypes) {
            assert($this->logger->debug('Skipping compression for response with empty content type') || true);

            return $response;
        }

        if (!preg_match($this->compressibleContentTypesRegex, $contentTypes)) {
            assert($this->logger->debug('Skipping compression for response with content type: {contentTypes}', ['contentTypes' => $contentTypes]) || true);

            return $response;
        }


        $weight = 0;
        $encoding = null;
        foreach (($request->getHeader('accept-encoding') ?? []) as $values) {
            $values = array_map("trim", explode(",", $values));
            foreach ($values as $value) {
                if (preg_match(self::ENCODING_REGEX, $value, $matches)) {
                    $quality = (float) ($matches[2] ?? 1);
                    if ($quality <= $weight) {
                        continue;
                    }

                    $weight = $quality;
                    $encoding = strtolower($matches[1]);
                }
            }
        }

        if (null === $encoding || '' === $encoding) {
            assert($this->logger->debug('Skipping compression for response with no acceptable encoding') || true);

            return $response;
        }

        if (self::IDENTITY_ENCODING === $encoding) {
            assert($this->logger->debug('Skipping compression for response with identity encoding') || true);

            return $response;
        }

        $mode = self::SUPPORTED_ENCODINGS[$encoding] ?? null;
        if (null === $mode) {
            assert($this->logger->debug('Skipping compression for response with unsupported encoding: {encoding}', ['encoding' => $encoding]) || true);

            return $response;
        }

        // we need to read the body, and determine if it's compressible
        $buffer = '';
        do {
            $chunk = $body->getChunk();
            if (null !== $chunk) {
                $buffer .= $chunk;

                if (isset($buffer[$this->minimumCompressionContentLength])) {
                    // the body meets the minimum size requirement
                    break;
                }
            } else {
                assert($this->logger->debug('Skipping compression for response with body of size {size}', [
                    'size' => strlen($buffer),
                ]) || true);

                // the response body is too small to compress
                return $response->withBody(Body::fromString($buffer));
            }
        } while (true);

        $context = deflate_init($mode, [
            'level' => $this->level,
            'memory' => $this->memory,
            'window' => $this->window,
        ]);

        if (false === $context) {
            throw new RuntimeException('Failed to initialize deflate context');
        }

        if ($contentLength !== null) {
            $response = $response->withHeader('X-Inflated-Content-Length', (string) $contentLength);
        }

        // we need to remove the content-length header, as the compressed body will have a different length
        $response = $response->withoutHeader('Content-Length');

        // add the content-encoding header
        $response = $response->withHeader('Content-Encoding', $encoding);

        // add the vary header
        $response = $response->withHeader('Vary', 'Accept-Encoding');

        if ($request->getProtocolVersion()->getMajorVersion() === 1) {
            // we need to close the connection for HTTP/1.x responses
            $response = $response->withHeader('Connection', 'close');
        }

        return $response->withBody(Body::fromIterable(
            self::read($context, $body, $buffer)
        ));
    }

    /**
     * Read the body and compress it.
     *
     * @param DeflateContext $context The deflate context.
     * @param BodyInterface $body The response body.
     * @param string $chunk The initial chunk of the response body.
     *
     * @return Generator<int, string, mixed, never>
     */
    private static function read(DeflateContext $context, BodyInterface $body, string $chunk): Generator
    {
        $handler = static function (int $code, string $message): never {
            throw new RuntimeException('failed to compress chunk: ' . $message, $code);
        };

        do {
            if ($chunk !== '') {
                set_error_handler($handler);

                try {
                    $chunk = deflate_add($context, $chunk, ZLIB_SYNC_FLUSH);
                } finally {
                    restore_error_handler();
                }

                if (false === $chunk) {
                    throw new RuntimeException('failed to compress chunk');
                }

                yield $chunk;
            }

            $chunk = $body->getChunk();
        } while ($chunk !== null);

        set_error_handler($handler);

        try {
            $chunk = deflate_add($context, '', ZLIB_FINISH);
        } finally {
            restore_error_handler();
        }

        if (false === $chunk) {
            throw new RuntimeException('failed to finish compression');
        }

        yield $chunk;
    }

    #[Override]
    public function getPriority(): int
    {
        return $this->priority;
    }
}
