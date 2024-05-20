<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\DependencyInjection\Factory\Form;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Message\Form\MultipartIncrementalFormParser;

/**
 * Factory for the multipart incremental form parser.
 *
 * @implements FactoryInterface<MultipartIncrementalFormParser>
 */
final readonly class MultipartIncrementalFormParserFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): MultipartIncrementalFormParser
    {
        return new MultipartIncrementalFormParser();
    }
}
