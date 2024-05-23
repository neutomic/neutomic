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
use Neu\Component\Http\Message\Form\MultipartParser;
use Neu\Component\Http\Message\Form\Parser;
use Neu\Component\Http\Message\Form\ParserInterface;
use Neu\Component\Http\Message\Form\StreamedParserInterface;
use Neu\Component\Http\Message\Form\UrlEncodedParser;

final readonly class MessageExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        $container->addDefinition(Definition::ofType(MultipartParser::class, new Factory\Form\MultipartParserFactory()));
        $container->addDefinition(Definition::ofType(UrlEncodedParser::class, new Factory\Form\UrlEncodedParserFactory()));

        $definition = Definition::ofType(Parser::class, new Factory\Form\ParserFactory());
        $definition->addAlias(ParserInterface::class);
        $definition->addAlias(StreamedParserInterface::class);

        $container->addDefinition($definition);
    }
}
