<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Handler;

use Amp\File;
use Amp\Log\StreamHandler;
use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\Processor\ProcessorInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a file stream handler.
 *
 * @implements FactoryInterface<StreamHandler>
 */
final readonly class FileHandlerFactory implements FactoryInterface
{
    /**
     * The file path for the log.
     */
    private string $file;

    /**
     * The logging level.
     */
    private int|string|Level $level;

    /**
     * Whether the handler should bubble.
     */
    private bool $bubble;

    /**
     * The formatter service identifier.
     *
     * @var non-empty-string|null
     */
    private ?string $formatter;

    /**
     * The processor service identifiers.
     *
     * @var list<non-empty-string>
     */
    private array $processors;

    /**
     * Create a new {@see FileHandlerFactory} instance.
     *
     * @param string $file The file path for the log.
     * @param Level $level The logging level.
     * @param bool $bubble Whether the handler should bubble.
     * @param non-empty-string|null $formatter The formatter service identifier.
     * @param list<non-empty-string> $processors The processor service identifiers.
     */
    public function __construct(string $file, null|int|string|Level $level = null, ?bool $bubble = null, ?string $formatter = null, ?array $processors = null)
    {
        $this->file = $file;
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
        $fileStream = File\openFile($this->file, mode: 'a');
        $handler = new StreamHandler($fileStream, $this->level, $this->bubble);

        if (null !== $this->formatter) {
            $handler->setFormatter($container->getTyped($this->formatter, FormatterInterface::class));
        }

        foreach ($this->processors as $processor) {
            $handler->pushProcessor($container->getTyped($processor, ProcessorInterface::class));
        }

        return $handler;
    }
}
