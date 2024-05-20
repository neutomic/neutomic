<?php

declare(strict_types=1);

namespace Neu\Bridge\Monolog\DependencyInjection\Factory\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\ScalarFormatter;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for the scalar formatter.
 *
 * @implements FactoryInterface<ScalarFormatter>
 */
final readonly class ScalarFormatterFactory implements FactoryInterface
{
    /**
     * The date format for log messages.
     */
    private string $dateFormat;

    /**
     * Create a new {@see ScalarFormatterFactory} instance.
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
        return new ScalarFormatter(
            dateFormat: $this->dateFormat
        );
    }
}
