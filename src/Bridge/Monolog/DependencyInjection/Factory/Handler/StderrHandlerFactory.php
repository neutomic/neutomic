<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Handler;

use Amp\ByteStream;
use Amp\Log\StreamHandler;
use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\Processor\ProcessorInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a stderr stream handler.
 *
 * @implements FactoryInterface<StreamHandler>
 */
final readonly class StderrHandlerFactory implements FactoryInterface
{
    /**
     * The logging level.
     */
    private null|int|string|Level $level;

    /**
     * Whether the handler should bubble.
     */
    private bool $bubble;

    /**
     * The formatter service identifier.
     */
    private ?string $formatter;

    /**
     * The processor service identifiers.
     *
     * @var list<non-empty-string>
     */
    private array $processors;

    /**
     * Create a new {@see StderrHandlerFactory} instance.
     *
     * @param null|int|string|Level $level The logging level.
     * @param bool|null $bubble Whether the handler should bubble.
     * @param non-empty-string|null $formatter The formatter service identifier.
     * @param list<non-empty-string>|null $processors The processor service identifiers.
     */
    public function __construct(null|int|string|Level $level = null, ?bool $bubble = null, ?string $formatter = null, ?array $processors = null)
    {
        $this->level = $level ?? Level::Debug;
        $this->bubble = $bubble ?? true;
        $this->formatter = $formatter;
        $this->processors = $processors ?? [];
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): object
    {
        $handler = new StreamHandler(ByteStream\getStderr(), $this->level, $this->bubble);

        if (null !== $this->formatter) {
            $handler->setFormatter($container->getTyped($this->formatter, FormatterInterface::class));
        }

        foreach ($this->processors as $processor) {
            $handler->pushProcessor($container->getTyped($processor, ProcessorInterface::class));
        }

        return $handler;
    }
}
