<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Form;

use Amp\Pipeline\Pipeline;
use Amp\Pipeline\Queue;
use Neu\Component\Http\Message\RequestInterface;
use Revolt\EventLoop;
use Throwable;

/**
 * Parses form data from HTTP requests.
 *
 * This parser supports both URL-encoded and multipart form data.
 */
final readonly class IncrementalFormParser implements IncrementalFormParserInterface
{
    /**
     * Parses the form data from the given HTTP request.
     *
     * @param RequestInterface $request The HTTP request containing form data.
     * @param null|ParseOptions $options Optional parsing options.
     *
     * @return FormInterface The parsed form data.
     */
    public function parse(RequestInterface $request, null|ParseOptions $options = null): FormInterface
    {
        $body = $request->getBody();
        if (null === $body) {
            // We don't have a body to parse.
            return new Form(Pipeline::fromIterable([])->getIterator());
        }

        $contentTypes = $request->getHeaderLine('content-type');
        if (null === $contentTypes) {
            return new Form(Pipeline::fromIterable([])->getIterator());
        }

        if (str_starts_with($contentTypes, UrlEncodedIncrementalFormParser::CONTENT_TYPE)) {
            $boundary = '';
        } else {
            $boundary = Internal\MultiPart\Parser::parseBoundary($contentTypes);
            if (null === $boundary) {
                return new Form(Pipeline::fromIterable([])->getIterator());
            }
        }

        $source = new Queue();
        $pipeline = $source->pipe();

        if ($options->bodySizeLimit !== null) {
            $body->upgradeSizeLimit($options->bodySizeLimit);
        }

        $options ??= new ParseOptions();
        EventLoop::queue(static function () use ($boundary, $source, $body, $options): void {
            try {
                if ('' !== $boundary) {
                    Internal\MultiPart\Parser::parseIncrementally($source, $body, $options, $boundary);
                } else {
                    Internal\UrlEncoded\Parser::parseIncrementally($source, $body, $options);
                }

                $source->complete();
            } catch (Throwable $e) {
                $source->error($e);
            }
        });

        return new Form($pipeline->getIterator());
    }
}
