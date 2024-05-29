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

namespace Neu\Framework\DependencyInjection\Factory\Middleware;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Framework\Middleware\CompressionMiddleware;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see CompressionMiddleware} instance.
 *
 * @implements FactoryInterface<CompressionMiddleware>
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class CompressionMiddlewareFactory implements FactoryInterface
{
    /**
     * The logger used to log events.
     *
     * @var non-empty-string
     */
    private string $logger;

    /**
     * The minimum length of compressible content.
     *
     * @var int<0, max>
     */
    private int $minimumCompressibleContentLength;

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
     *
     * @var int
     */
    private int $priority;

    /**
     * Create a new {@see CompressionMiddlewareFactory} instance.
     *
     * @param null|non-empty-string $logger The logger used to log events.
     * @param null|int<0, max> $minimumCompressibleContentLength The minimum length of compressible content.
     * @param null|non-empty-string $compressibleContentTypesRegex The regular expression used to match compressible content types.
     * @param null|int<-1, 9> $level The level of compression.
     * @param null|int<1, 9> $memory The memory level.
     * @param null|int<8, 15> $window The window size.
     * @param null|int $priority The priority of the middleware.
     */
    public function __construct(null|string $logger = null, null|int $minimumCompressibleContentLength = null, null|string $compressibleContentTypesRegex = null, null|int $level = null, null|int $memory = null, null|int $window = null, null|int $priority = null)
    {
        $this->logger = $logger ?? LoggerInterface::class;
        $this->minimumCompressibleContentLength = $minimumCompressibleContentLength ?? CompressionMiddleware::DEFAULT_MINIMUM_COMPRESSIBLE_CONTENT_LENGTH;
        $this->compressibleContentTypesRegex = $compressibleContentTypesRegex ?? CompressionMiddleware::DEFAULT_COMPRESSIBLE_CONTENT_TYPES_REGEX;
        $this->level = $level ?? CompressionMiddleware::DEFAULT_LEVEL;
        $this->memory = $memory ?? CompressionMiddleware::DEFAULT_MEMORY;
        $this->window = $window ?? CompressionMiddleware::DEFAULT_WINDOW;
        $this->priority = $priority ?? CompressionMiddleware::PRIORITY;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): CompressionMiddleware
    {
        return new CompressionMiddleware(
            $container->getTyped($this->logger, LoggerInterface::class),
            $this->minimumCompressibleContentLength,
            $this->compressibleContentTypesRegex,
            $this->level,
            $this->memory,
            $this->window,
            $this->priority,
        );
    }
}
