<?php

declare(strict_types=1);

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
                    $buffer .= $token->value;
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
