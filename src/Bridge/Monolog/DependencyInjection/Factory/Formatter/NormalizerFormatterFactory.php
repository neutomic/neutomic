<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for the normalizer formatter.
 *
 * @implements FactoryInterface<NormalizerFormatter>
 */
final readonly class NormalizerFormatterFactory implements FactoryInterface
{
    /**
     * The date format for log messages.
     */
    private string $dateFormat;

    /**
     * Create a new {@see NormalizerFormatterFactory} instance.
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
        return new NormalizerFormatter(
            dateFormat: $this->dateFormat
        );
    }
}
