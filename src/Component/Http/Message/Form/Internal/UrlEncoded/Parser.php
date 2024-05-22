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

use Amp\Pipeline\DisposedException;
use Amp\Pipeline\Queue;
use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Body;
use Neu\Component\Http\Message\Form\Field;
use Neu\Component\Http\Message\Form\ParseOptions;
use Neu\Component\Http\Message\RequestBodyInterface;
use Neu\Component\Http\Message\StatusCode;
use Throwable;

/**
 * @internal
 *
 * @psalm-suppress ArgumentTypeCoercion
 * @psalm-suppress MixedMethodCall
 * @psalm-suppress PossiblyNullArgument
 * @psalm-suppress PossiblyNullPropertyFetch
 */
final readonly class Parser
{
    /**
     * Parses the form data incrementally from the request body.
     *
     * @param Queue $source The source queue to push parsed fields.
     * @param RequestBodyInterface $body The request body containing URL-encoded form data.
     * @param ParseOptions $options The parsing options.
     *
     * @throws HttpException if the maximum number of fields is exceeded.
     * @throws Throwable if an error occurs during parsing.
     */
    public static function parseIncrementally(Queue $source, RequestBodyInterface $body, ParseOptions $options): void
    {
        $fieldCount = 0;
        $buffer = '';
        $queue = null;
        $tokens = Tokenizer::tokenize($body);
        try {
            while ($tokens->valid()) {
                $token = $tokens->current();
                $tokens->next();

                if ($token->type === TokenType::String) {
                    $buffer .= (string) $token->value;
                } elseif ($token->type === TokenType::Equals) {
                    if ('' === $buffer) {
                        // skip till next ampersand
                        while ($tokens->valid()) {
                            $token = $tokens->current();
                            $tokens->next();

                            if ($token->type === TokenType::Ampersand) {
                                break;
                            }
                        }

                        continue;
                    }

                    $field = $buffer;
                    $buffer = '';

                    if ($fieldCount++ === $options->fieldCountLimit) {
                        throw new HttpException(StatusCode::PayloadTooLarge, message: 'Maximum number of fields exceeded.');
                    }

                    /** @var Queue<string> $queue */
                    $queue = new Queue();
                    $future = $source->pushAsync(Field::create(urldecode($field), [], Body::fromIterable($queue->iterate())));
                    while ($tokens->valid()) {
                        $token = $tokens->current();
                        $tokens->next();

                        if ($token->type === TokenType::Ampersand) {
                            $queue->complete();
                            $queue = null;
                            break;
                        }

                        try {
                            $queue->push(urldecode($token->value));
                        } catch (DisposedException) {
                            // Ignore and continue consuming this field.
                        }
                    }

                    $queue?->complete();
                    $queue = null;
                    $future->await();
                } elseif ($token->type === TokenType::Ampersand) {
                    if ('' !== $buffer) {
                        if ($fieldCount++ === $options->fieldCountLimit) {
                            throw new HttpException(StatusCode::PayloadTooLarge, message: 'Maximum number of fields exceeded.');
                        }

                        $source->push(Field::create(urldecode($buffer), [], Body::fromString('')));
                        $buffer = '';
                    }
                }
            }
        } catch (Throwable $e) {
            $queue?->error($e);

            throw $e;
        }
    }
}
