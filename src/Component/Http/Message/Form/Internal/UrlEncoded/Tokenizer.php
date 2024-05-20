<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Form\Internal\UrlEncoded;

use Iterator;
use Neu\Component\Http\Message\RequestBodyInterface;

use function strlen;
use function substr;

final readonly class Tokenizer
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
