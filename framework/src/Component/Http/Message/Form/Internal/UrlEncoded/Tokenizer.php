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

namespace Neu\Component\Http\Message\Form\Internal\UrlEncoded;

use Iterator;
use Neu\Component\Http\Message\RequestBodyInterface;

use function strlen;
use function substr;

enum Tokenizer
{
    /**
     * @return Iterator<Token>
     */
    public static function tokenize(RequestBodyInterface $body): Iterator
    {
        while (true) {
            $chunk = $body->getChunk();
            if (null === $chunk) {
                return;
            }

            $length = strlen($chunk);
            for ($i = 0; $i < $length; $i++) {
                $char = $chunk[$i];
                if ($char === '=') {
                    yield new Token(TokenType::Equals, $char);
                } elseif ($char === '&') {
                    yield new Token(TokenType::Ampersand, $char);
                } else {
                    $start = $i;
                    while ($i < $length && $chunk[$i] !== '&' && $chunk[$i] !== '=') {
                        $i++;
                    }

                    yield new Token(TokenType::String, substr($chunk, $start, $i - $start));

                    $i--;
                }
            }
        }
    }
}
