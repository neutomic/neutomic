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
