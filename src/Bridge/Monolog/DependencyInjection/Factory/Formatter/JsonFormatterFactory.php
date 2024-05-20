<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Formatter;

use Monolog\Formatter\JsonFormatter;
use Neu\Component\DependencyInjection\ContainerInterface;
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
     * @param ?int $batchMode The batch mode for the JSON formatter.
     * @param ?bool $appendNewline Whether to append a newline to each log entry.
     * @param ?bool $ignoreEmptyContextAndExtra Whether to ignore empty context and extra data.
     * @param ?bool $includeStacktraces Whether to include stack traces in the log entries.
     */
    public function __construct(?int $batchMode = null, ?bool $appendNewline = null, ?bool $ignoreEmptyContextAndExtra = null, ?bool $includeStacktraces = null)
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
        return new JsonFormatter(
            batchMode: $this->batchMode,
            appendNewline: $this->appendNewline,
            ignoreEmptyContextAndExtra: $this->ignoreEmptyContextAndExtra,
            includeStacktraces: $this->includeStacktraces,
        );
    }
}
