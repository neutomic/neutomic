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

namespace Neu\Bridge\Monolog\DependencyInjection\Factory;

use DateTimeZone;
use Exception;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\InvalidArgumentException;
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
     *
     * @var non-empty-string
     */
    private string $channel;

    /**
     * The timezone for the logger.
     */
    private null|DateTimeZone $timezone;

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
     * @param non-empty-string $channel The channel name for the logger.
     * @param non-empty-string|null $timezone The timezone for the logger.
     * @param list<non-empty-string>|null $handlers The handler service identifiers.
     * @param list<non-empty-string>|null $processors The processor service identifiers.
     * @param bool|null $useLoggingLoopDetection Whether to use logging loop detection.
     * @param bool|null $useMicrosecondTimestamps Whether to use microsecond timestamps.
     *
     * @throws InvalidArgumentException If the timezone is invalid.
     */
    public function __construct(string $channel, null|string $timezone = null, null|array $handlers = null, null|array $processors = null, null|bool $useLoggingLoopDetection = null, null|bool $useMicrosecondTimestamps = null)
    {
        if (null !== $timezone) {
            try {
                $timezone = new DateTimeZone($timezone);
            } catch (Exception $e) {
                throw new InvalidArgumentException('Invalid timezone provided', previous: $e);
            }
        }

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
