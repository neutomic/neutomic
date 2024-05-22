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

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Formatter;

use Amp\Log\ConsoleFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for the console formatter.
 *
 * @implements FactoryInterface<ConsoleFormatter>
 */
final readonly class ConsoleFormatterFactory implements FactoryInterface
{
    /**
     * The format of the log messages.
     */
    private string $format;

    /**
     * The date format for log messages.
     */
    private string $dateFormat;

    /**
     * Whether to allow inline line breaks in log messages.
     */
    private bool $allowInlineLineBreaks;

    /**
     * Whether to ignore empty context and extra data in log messages.
     */
    private bool $ignoreEmptyContextAndExtra;

    /**
     * Create a new {@see ConsoleFormatterFactory} instance.
     *
     * @param ?string $format The format of the log messages.
     * @param ?string $dateFormat The date format for log messages.
     * @param bool $allowInlineLineBreaks Whether to allow inline line breaks in log messages.
     * @param bool $ignoreEmptyContextAndExtra Whether to ignore empty context and extra data in log messages.
     */
    public function __construct(null|string $format = null, null|string $dateFormat = null, null|bool $allowInlineLineBreaks = null, null|bool $ignoreEmptyContextAndExtra = null)
    {
        $this->format = $format ?? ConsoleFormatter::DEFAULT_FORMAT;
        $this->dateFormat = $dateFormat ?? NormalizerFormatter::SIMPLE_DATE;
        $this->allowInlineLineBreaks = $allowInlineLineBreaks ?? false;
        $this->ignoreEmptyContextAndExtra = $ignoreEmptyContextAndExtra ?? false;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): object
    {
        return new ConsoleFormatter(
            format: $this->format,
            dateFormat: $this->dateFormat,
            allowInlineLineBreaks: $this->allowInlineLineBreaks,
            ignoreEmptyContextAndExtra: $this->ignoreEmptyContextAndExtra,
        );
    }
}
