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

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for the line formatter.
 *
 * @implements FactoryInterface<LineFormatter>
 */
final readonly class LineFormatterFactory implements FactoryInterface
{
    /**
     * The format for the log messages.
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
     * Whether to ignore empty context and extra data.
     */
    private bool $ignoreEmptyContextAndExtra;

    /**
     * Whether to include stack traces in the log entries.
     */
    private bool $includeStacktraces;

    /**
     * Create a new {@see LineFormatterFactory} instance.
     *
     * @param ?string $format The format for the log messages.
     * @param ?string $dateFormat The date format for log messages.
     * @param ?bool $allowInlineLineBreaks Whether to allow inline line breaks in log messages.
     * @param ?bool $ignoreEmptyContextAndExtra Whether to ignore empty context and extra data.
     * @param ?bool $includeStacktraces Whether to include stack traces in the log entries.
     */
    public function __construct(null|string $format = null, null|string $dateFormat = null, null|bool $allowInlineLineBreaks = null, null|bool $ignoreEmptyContextAndExtra = null, null|bool $includeStacktraces = null)
    {
        $this->format = $format ?? LineFormatter::SIMPLE_FORMAT;
        $this->dateFormat = $dateFormat ?? NormalizerFormatter::SIMPLE_DATE;
        $this->allowInlineLineBreaks = $allowInlineLineBreaks ?? false;
        $this->ignoreEmptyContextAndExtra = $ignoreEmptyContextAndExtra ?? false;
        $this->includeStacktraces = $includeStacktraces ?? false;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): object
    {
        try {
            return new LineFormatter(
                format: $this->format,
                dateFormat: $this->dateFormat,
                allowInlineLineBreaks: $this->allowInlineLineBreaks,
                ignoreEmptyContextAndExtra: $this->ignoreEmptyContextAndExtra,
                includeStacktraces: $this->includeStacktraces,
            );
        } catch (\RuntimeException $e) {
            throw new RuntimeException(message: 'Failed to create the line formatter.', previous: $e);
        }
    }
}
