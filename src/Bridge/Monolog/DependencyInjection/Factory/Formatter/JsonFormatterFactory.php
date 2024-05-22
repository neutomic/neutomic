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

use Monolog\Formatter\JsonFormatter;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for the json formatter.
 *
 * @implements FactoryInterface<JsonFormatter>
 */
final readonly class JsonFormatterFactory implements FactoryInterface
{
    /**
     * The batch mode for the JSON formatter.
     *
     * @var 1|2
     */
    private int $batchMode;

    /**
     * Whether to append a newline to each log entry.
     */
    private bool $appendNewline;

    /**
     * Whether to ignore empty context and extra data.
     */
    private bool $ignoreEmptyContextAndExtra;

    /**
     * Whether to include stack traces in the log entries.
     */
    private bool $includeStacktraces;

    /**
     * Create a new {@see JsonFormatterFactory} instance.
     *
     * @param null|1|2 $batchMode The batch mode for the JSON formatter.
     * @param null|bool $appendNewline Whether to append a newline to each log entry.
     * @param null|bool $ignoreEmptyContextAndExtra Whether to ignore empty context and extra data.
     * @param null|bool $includeStacktraces Whether to include stack traces in the log entries.
     */
    public function __construct(null|int $batchMode = null, null|bool $appendNewline = null, null|bool $ignoreEmptyContextAndExtra = null, null|bool $includeStacktraces = null)
    {
        $this->batchMode = $batchMode ?? JsonFormatter::BATCH_MODE_JSON;
        $this->appendNewline = $appendNewline ?? true;
        $this->ignoreEmptyContextAndExtra = $ignoreEmptyContextAndExtra ?? false;
        $this->includeStacktraces = $includeStacktraces ?? false;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): object
    {
        try {
            return new JsonFormatter(
                batchMode: $this->batchMode,
                appendNewline: $this->appendNewline,
                ignoreEmptyContextAndExtra: $this->ignoreEmptyContextAndExtra,
                includeStacktraces: $this->includeStacktraces,
            );
        } catch (\RuntimeException $e) {
            throw new RuntimeException(message: 'Failed to create the json formatter.', previous: $e);
        }
    }
}
