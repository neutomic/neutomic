<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a logger instance.
 *
 * @implements FactoryInterface<Logger>
 */
final readonly class LoggerFactory implements FactoryInterface
{
    /**
     * The channel name for the logger.
     */
    private string $channel;

    /**
     * The timezone for the logger.
     */
    private ?string $timezone;

    /**
     * The handler service identifiers.
     *
     * @var list<non-empty-string>
     */
    private array $handlers;

    /**
     * The processor service identifiers.
     *
     * @var list<non-empty-string>
     */
    private array $processors;

    /**
     * Whether to use logging loop detection.
     */
    private bool $useLoggingLoopDetection;

    /**
     * Whether to use microsecond timestamps.
     */
    private bool $useMicrosecondTimestamps;

    /**
     * Create a new {@see LoggerFactory} instance.
     *
     * @param string $channel The channel name for the logger.
     * @param string|null $timezone The timezone for the logger.
     * @param list<non-empty-string>|null $handlers The handler service identifiers.
     * @param list<non-empty-string>|null $processors The processor service identifiers.
     * @param bool|null $useLoggingLoopDetection Whether to use logging loop detection.
     * @param bool|null $useMicrosecondTimestamps Whether to use microsecond timestamps.
     */
    public function __construct(string $channel, ?string $timezone = null, ?array $handlers = null, ?array $processors = null, ?bool $useLoggingLoopDetection = null, ?bool $useMicrosecondTimestamps = null)
    {
        $this->channel = $channel;
        $this->timezone = $timezone;
        $this->handlers = $handlers ?? [];
        $this->processors = $processors ?? [];
        $this->useLoggingLoopDetection = $useLoggingLoopDetection ?? true;
        $this->useMicrosecondTimestamps = $useMicrosecondTimestamps ?? true;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): object
    {
        $logger = new Logger(name: $this->channel, timezone: $this->timezone);
        foreach ($this->handlers as $handler) {
            $logger->pushHandler($container->getTyped($handler, HandlerInterface::class));
        }

        foreach ($this->processors as $processor) {
            $logger->pushProcessor($container->getTyped($processor, ProcessorInterface::class));
        }

        $logger->useLoggingLoopDetection($this->useLoggingLoopDetection);
        $logger->useMicrosecondTimestamps($this->useMicrosecondTimestamps);

        return $logger;
    }
}
