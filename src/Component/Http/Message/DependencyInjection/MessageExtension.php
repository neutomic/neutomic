<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\DependencyInjection;

use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\Http\Message\Form\IncrementalFormParser;
use Neu\Component\Http\Message\Form\IncrementalFormParserInterface;
use Neu\Component\Http\Message\Form\MultipartIncrementalFormParser;
use Neu\Component\Http\Message\Form\UrlEncodedIncrementalFormParser;

final readonly class MessageExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        $container->addDefinitions([
            Definition::ofType(MultipartIncrementalFormParser::class, new Factory\Form\MultipartIncrementalFormParserFactory()),
            Definition::ofType(UrlEncodedIncrementalFormParser::class, new Factory\Form\UrlEncodedIncrementalFormParserFactory()),
            Definition::ofType(IncrementalFormParser::class, new Factory\Form\IncrementalFormParserFactory()),
        ]);

        $container->getDefinition(IncrementalFormParser::class)->addAlias(IncrementalFormParserInterface::class);
    }
}
