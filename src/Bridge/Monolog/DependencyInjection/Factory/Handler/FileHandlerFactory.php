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

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Handler;

use Amp\File;
use Amp\File\FilesystemException;
use Amp\Log\StreamHandler;
use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\Processor\ProcessorInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a file stream handler.
 *
 * @implements FactoryInterface<StreamHandler>
 *
 * @psalm-suppress ArgumentTypeCoercion
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
    private null|int|string|Level $level;

    /**
     * Whether the handler should bubble.
     */
    private bool $bubble;

    /**
     * The formatter service identifier.
     *
     * @var non-empty-string|null
     */
    private null|string $formatter;

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
     * @param null|int|string|Level $level The logging level.
     * @param null|bool $bubble Whether the handler should bubble.
     * @param null|non-empty-string $formatter The formatter service identifier.
     * @param null|list<non-empty-string> $processors The processor service identifiers.
     */
    public function __construct(string $file, null|int|string|Level $level = null, null|bool $bubble = null, null|string $formatter = null, null|array $processors = null)
    {
        $this->file = $file;
        $this->level = $level;
        $this->bubble = $bubble ?? true;
        $this->formatter = $formatter;
        $this->processors = $processors ?? [];
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): object
    {
        $level = $this->level;
        if ($container->getProject()->debug) {
            $level = Level::Debug;
        } elseif (null === $level) {
            $level = $container->getProject()->mode->isProduction() ? Level::Notice : Level::Info;
        }

        try {
            $fileStream = File\openFile($this->file, mode: 'a');
        } catch (FilesystemException $e) {
            throw new RuntimeException('Failed to open log file', 0, $e);
        }

        $handler = new StreamHandler($fileStream, $level, $this->bubble);
        if (null !== $this->formatter) {
            $handler->setFormatter($container->getTyped($this->formatter, FormatterInterface::class));
        }

        foreach ($this->processors as $processor) {
            $handler->pushProcessor($container->getTyped($processor, ProcessorInterface::class));
        }

        return $handler;
    }
}
