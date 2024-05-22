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
        $container->addDefinition(Definition::ofType(MultipartIncrementalFormParser::class, new Factory\Form\MultipartIncrementalFormParserFactory()));
        $container->addDefinition(Definition::ofType(UrlEncodedIncrementalFormParser::class, new Factory\Form\UrlEncodedIncrementalFormParserFactory()));
        $container->addDefinition(Definition::ofType(IncrementalFormParser::class, new Factory\Form\IncrementalFormParserFactory()));

        $container->getDefinition(IncrementalFormParser::class)->addAlias(IncrementalFormParserInterface::class);
    }
}
