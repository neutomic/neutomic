<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\DependencyInjection\Factory\Form;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Message\Form\UrlEncodedIncrementalFormParser;

/**
 * Factory for the url-encoded incremental form parser.
 *
 * @implements FactoryInterface<UrlEncodedIncrementalFormParser>
 */
final readonly class UrlEncodedIncrementalFormParserFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): UrlEncodedIncrementalFormParser
    {
        return new UrlEncodedIncrementalFormParser();
    }
}
