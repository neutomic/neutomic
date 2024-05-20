<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\DependencyInjection\Factory\Form;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Message\Form\IncrementalFormParser;

/**
 * Factory for the incremental form parser.
 *
 * @implements FactoryInterface<IncrementalFormParser>
 */
final readonly class IncrementalFormParserFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): IncrementalFormParser
    {
        return new IncrementalFormParser();
    }
}
