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

use Amp\ByteStream;
use Amp\Log\StreamHandler;
use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\Processor\ProcessorInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a stdout stream handler.
 *
 * @implements FactoryInterface<StreamHandler>
 *
 * @psalm-suppress ArgumentTypeCoercion
 */
final readonly class StdoutHandlerFactory implements FactoryInterface
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
     * Create a new {@see StdoutHandlerFactory} instance.
     *
     * @param null|int|string|Level $level The logging level.
     * @param bool|null $bubble Whether the handler should bubble.
     * @param non-empty-string|null $formatter The formatter service identifier.
     * @param list<non-empty-string>|null $processors The processor service identifiers.
     */
    public function __construct(null|int|string|Level $level = null, null|bool $bubble = null, null|string $formatter = null, null|array $processors = null)
    {
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

        $handler = new StreamHandler(ByteStream\getStdout(), $level, $this->bubble);

        if (null !== $this->formatter) {
            $handler->setFormatter($container->getTyped($this->formatter, FormatterInterface::class));
        }

        foreach ($this->processors as $processor) {
            $handler->pushProcessor($container->getTyped($processor, ProcessorInterface::class));
        }

        return $handler;
    }
}
