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

/**
 * Parses URL-encoded form data from HTTP requests.
 */
final readonly class UrlEncodedParser implements ParserInterface, StreamedParserInterface
{
    /**
     * @inheritDoc
     */
    public function parse(RequestInterface $request, null|ParseOptions $options = null): FormInterface
    {
        $body = $request->getBody();
        $options ??= new ParseOptions();
        if (!Internal\UrlEncoded\Parser::isSupported($request, $body)) {
            return new Form([]);
        }

        /** @var RequestBodyInterface $body - body is not null at this stage */
        if ($options->bodySizeLimit !== null) {
            $body->upgradeSizeLimit($options->bodySizeLimit);
        }

        return new Form(
            Internal\UrlEncoded\Parser::parseInFull($body, $options)
        );
    }

    /**
     * @inheritDoc
     */
    public function parseStreamed(RequestInterface $request, null|ParseOptions $options = null): StreamedFormInterface
    {
        $body = $request->getBody();
        $options ??= new ParseOptions();
        if (!Internal\UrlEncoded\Parser::isSupported($request, $body)) {
            return new StreamedForm(Pipeline::fromIterable([])->getIterator());
        }

        /** @var RequestBodyInterface $body - body is not null at this stage */
        if ($options->bodySizeLimit !== null) {
            $body->upgradeSizeLimit($options->bodySizeLimit);
        }

        /** @var Queue<FieldInterface> $source */
        $source = new Queue();
        $pipeline = $source->pipe();

        EventLoop::queue(static function () use ($source, $body, $options): void {
            try {
                Internal\UrlEncoded\Parser::parseStreaming($source, $body, $options);

                $source->complete();
            } catch (Throwable $e) {
                $source->error($e);
            }
        });

        return new StreamedForm($pipeline->getIterator());
    }
}
