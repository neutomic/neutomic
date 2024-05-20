<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Formatter;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for the html formatter.
 *
 * @implements FactoryInterface<HtmlFormatter>
 */
final readonly class HtmlFormatterFactory implements FactoryInterface
{
    /**
     * The date format for log messages.
     */
    private string $dateFormat;

    /**
     * Create a new {@see HtmlFormatterFactory} instance.
     *
     * @param ?string $dateFormat The date format for log messages.
     */
    public function __construct(?string $dateFormat = null)
    {
        $this->dateFormat = $dateFormat ?? NormalizerFormatter::SIMPLE_DATE;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): object
    {
        return new HtmlFormatter(
            dateFormat: $this->dateFormat,
        );
    }
}
