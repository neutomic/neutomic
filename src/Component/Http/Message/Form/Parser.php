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

namespace Neu\Component\Http\Message\Form;

use Amp\Pipeline\Pipeline;
use Amp\Pipeline\Queue;
use Neu\Component\Http\Message\RequestBodyInterface;
use Neu\Component\Http\Message\RequestInterface;
use Revolt\EventLoop;
use Throwable;
use Override;

/**
 * Parses form data from HTTP requests.
 *
 * This parser supports both URL-encoded and multipart form data.
 *
 * @see ParserInterface
 * @see StreamedParserInterface
 */
final readonly class Parser implements ParserInterface, StreamedParserInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function parse(RequestInterface $request, null|ParseOptions $options = null): FormInterface
    {
        $body = $request->getBody();
        $options ??= new ParseOptions();
        $boundary = null;

        // check if the request is URL-encoded first, as it's more common, and faster.
        if (!Internal\UrlEncoded\Parser::isSupported($request, $body)) {
            // if not, check if it's multipart
            $boundary = Internal\MultiPart\Parser::getBoundary($request, $body);
            // if it's not multipart, return an empty form
            if (null === $boundary) {
                return new Form([]);
            }
        }

        /** @var RequestBodyInterface $body - body is not null at this stage */
        if ($options->bodySizeLimit !== null) {
            $body->upgradeSizeLimit($options->bodySizeLimit);
        }

        // Parse the form data from the request body, using the appropriate parser.
        return new Form(
            null === $boundary ?
                Internal\UrlEncoded\Parser::parseInFull($body, $options) :
                Internal\MultiPart\Parser::parseInFull($body, $options, $boundary)
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function parseStreamed(RequestInterface $request, null|ParseOptions $options = null): StreamedFormInterface
    {
        $body = $request->getBody();
        $options ??= new ParseOptions();
        $boundary = null;

        // check if the request is URL-encoded first, as it's more common, and faster.
        if (!Internal\UrlEncoded\Parser::isSupported($request, $body)) {
            // if not, check if it's multipart
            $boundary = Internal\MultiPart\Parser::getBoundary($request, $body);
            // if it's not multipart, return an empty form
            if (null === $boundary) {
                return new StreamedForm(Pipeline::fromIterable([])->getIterator());
            }
        }

        /** @var RequestBodyInterface $body - body is not null at this stage */
        if ($options->bodySizeLimit !== null) {
            $body->upgradeSizeLimit($options->bodySizeLimit);
        }

        /** @var Queue<FieldInterface> $source */
        $source = new Queue();
        $pipeline = $source->pipe();

        EventLoop::queue(static function () use ($boundary, $source, $body, $options): void {
            try {
                if (null !== $boundary) {
                    Internal\MultiPart\Parser::parseIncrementally($source, $body, $options, $boundary);
                } else {
                    Internal\UrlEncoded\Parser::parseStreaming($source, $body, $options);
                }

                $source->complete();
            } catch (Throwable $e) {
                $source->error($e);
            }
        });

        return new StreamedForm($pipeline->getIterator());
    }
}
