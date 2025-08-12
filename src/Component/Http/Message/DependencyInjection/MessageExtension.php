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

use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\DependencyInjection\RegistryInterface;
use Neu\Component\Http\Message\Form\MultipartParser;
use Neu\Component\Http\Message\Form\Parser;
use Neu\Component\Http\Message\Form\ParserInterface;
use Neu\Component\Http\Message\Form\StreamedParserInterface;
use Neu\Component\Http\Message\Form\UrlEncodedParser;
use Override;

final readonly class MessageExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function register(RegistryInterface $registry, DocumentInterface $configurations): void
    {
        $registry->addDefinition(Definition::ofType(MultipartParser::class, new Factory\Form\MultipartParserFactory()));
        $registry->addDefinition(Definition::ofType(UrlEncodedParser::class, new Factory\Form\UrlEncodedParserFactory()));
        $registry->addDefinition(Definition::ofType(Parser::class, new Factory\Form\ParserFactory()));

        $registry->getDefinition(Parser::class)->addAlias(ParserInterface::class);
        $registry->getDefinition(Parser::class)->addAlias(StreamedParserInterface::class);
    }
}
