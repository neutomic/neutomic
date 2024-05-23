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
use Neu\Component\Http\Message\Form\FieldInterface;
use Neu\Component\Http\Message\Form\ParseOptions;
use Neu\Component\Http\Message\RequestBodyInterface;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\StatusCode;
use Psl\Str;
use Throwable;

use function count;
use function urldecode;

/**
 * @internal
 *
 * @psalm-suppress ArgumentTypeCoercion
 * @psalm-suppress MixedMethodCall
 * @psalm-suppress PossiblyNullArgument
 * @psalm-suppress PossiblyNullPropertyFetch
 */
enum Parser
{
    public const string CONTENT_TYPE = 'application/x-www-form-urlencoded';

    /**
     * Checks if the request body is URL-encoded form data.
     *
     * @psalm-assert-if-true RequestBodyInterface $body
     */
    public static function isSupported(RequestInterface $request, null|RequestBodyInterface $body): bool
    {
        if (null === $body) {
            // We don't have a body to parse.
            return false;
        }

        $contentTypes = $request->getHeaderLine('content-type');
        if (null === $contentTypes || !Str\starts_with($contentTypes, self::CONTENT_TYPE)) {
            return false;
        }

        return true;
    }

    /**
     * Parses the form data in full from the request body.
     *
     * @param RequestBodyInterface $body The request body containing URL-encoded form data.
     * @param ParseOptions $options The parsing options.
     *
     * @throws HttpException if the maximum number of fields is exceeded.
     *
     * @return list<FieldInterface> The parsed fields.
     */
    public static function parseInFull(RequestBodyInterface $body, ParseOptions $options): array
    {
        $fields = [];
        $buffer = '';
        $tokens = Tokenizer::tokenize($body);
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

                if (count($fields) === $options->fieldCountLimit) {
                    throw new HttpException(
                        statusCode: StatusCode::PayloadTooLarge,
                        message: 'The number of fields in the form data exceeds the limit of ' . ((string) $options->fieldCountLimit) . '.',
                    );
                }

                $value = '';
                while ($tokens->valid()) {
                    $token = $tokens->current();
                    $tokens->next();

                    if ($token->type === TokenType::Ampersand) {
                        break;
                    }

                    $value .= urldecode($token->value);
                }

                /** @var non-empty-string $name */
                $name = urldecode($field);
                $fields[] = Field::create($name, [], Body::fromString(urldecode($value)));
            } elseif ($token->type === TokenType::Ampersand) {
                if ('' !== $buffer) {
                    if (count($fields) === $options->fieldCountLimit) {
                        throw new HttpException(
                            statusCode: StatusCode::PayloadTooLarge,
                            message: 'The number of fields in the form data exceeds the limit of ' . ((string) $options->fieldCountLimit) . '.',
                        );
                    }

                    /** @var non-empty-string $name */
                    $name = urldecode($buffer);
                    $fields[] = Field::create($name, [], Body::fromString(''));
                    $buffer = '';
                }
            }
        }

        return $fields;
    }

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
    public static function parseStreaming(Queue $source, RequestBodyInterface $body, ParseOptions $options): void
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
                        throw new HttpException(
                            statusCode: StatusCode::PayloadTooLarge,
                            message: 'The number of fields in the form data exceeds the limit of ' . ((string) $options->fieldCountLimit) . '.',
                        );
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
                            throw new HttpException(
                                StatusCode::PayloadTooLarge,
                                message: 'The number of fields in the form data exceeds the limit of ' . ((string) $options->fieldCountLimit) . '.',
                            );
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
