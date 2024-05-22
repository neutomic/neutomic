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

use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
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
    public function __construct(null|string $dateFormat = null)
    {
        $this->dateFormat = $dateFormat ?? NormalizerFormatter::SIMPLE_DATE;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): object
    {
        try {
            return new HtmlFormatter(
                dateFormat: $this->dateFormat,
            );
        } catch (\RuntimeException $e) {
            throw new RuntimeException(message: 'Failed to create the html formatter.', previous: $e);
        }
    }
}
