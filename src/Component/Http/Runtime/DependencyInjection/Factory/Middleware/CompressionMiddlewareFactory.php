<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Runtime\Middleware\CompressionMiddleware;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see CompressionMiddleware} instance.
 *
 * @implements FactoryInterface<CompressionMiddleware>
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
     *
     * @var int
     */
    private int $priority;

    /**
     * Create a new {@see CompressionMiddlewareFactory} instance.
     *
     * @param null|non-empty-string $logger The logger used to log events.
     * @param null|int<0, max> $minimumCompressionContentLength The minimum length of compressible content.
     * @param null|non-empty-string $compressibleContentTypesRegex The regular expression used to match compressible content types.
     * @param null|int<-1, 9> $level The level of compression.
     * @param null|int<1, 9> $memory The memory level.
     * @param null|int<8, 15> $window The window size.
     * @param null|int $priority The priority of the middleware.
     */
    public function __construct(?string $logger = null, ?int $minimumCompressionContentLength = null, ?string $compressibleContentTypesRegex = null, ?int $level = null, ?int $memory = null, ?int $window = null, ?int $priority = null)
    {
        $this->logger = $logger ?? LoggerInterface::class;
        $this->minimumCompressionContentLength = $minimumCompressionContentLength ?? CompressionMiddleware::DEFAULT_MINIMUM_COMPRESSIBLE_CONTENT_LENGTH;
        $this->compressibleContentTypesRegex = $compressibleContentTypesRegex ?? CompressionMiddleware::DEFAULT_COMPRESSIBLE_CONTENT_TYPES_REGEX;
        $this->level = $level ?? CompressionMiddleware::DEFAULT_LEVEL;
        $this->memory = $memory ?? CompressionMiddleware::DEFAULT_MEMORY;
        $this->window = $window ?? CompressionMiddleware::DEFAULT_WINDOW;
        $this->priority = $priority ?? CompressionMiddleware::PRIORITY;
    }

    public function __invoke(ContainerInterface $container): CompressionMiddleware
    {
        return new CompressionMiddleware(
            $container->getTyped($this->logger, LoggerInterface::class),
            $this->minimumCompressionContentLength,
            $this->compressibleContentTypesRegex,
            $this->level,
            $this->memory,
            $this->window,
            $this->priority,
        );
    }
}
