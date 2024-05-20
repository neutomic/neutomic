<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Form;

use Amp\Pipeline\Pipeline;
use Amp\Pipeline\Queue;
use Neu\Component\Http\Message\RequestInterface;
use Revolt\EventLoop;
use Throwable;

/**
 * Parses multipart form data from HTTP requests.
 */
final readonly class MultipartIncrementalFormParser implements IncrementalFormParserInterface
{
    /**
     * Parses the form data from the given HTTP request.
     *
     * @param RequestInterface $request The HTTP request containing multipart form data.
     * @param null|ParseOptions $options Optional parsing options.
     *
     * @return FormInterface The parsed form data.
     */
    public function parse(RequestInterface $request, ?ParseOptions $options = null): FormInterface
    {
        $contentTypes = $request->getHeaderLine('content-type');
        if (null === $contentTypes) {
            // No content type provided.
            return new Form(Pipeline::fromIterable([])->getIterator());
        }

        $body = $request->getBody();
        if (null === $body) {
            // We don't have a body to parse.
            return new Form(Pipeline::fromIterable([])->getIterator());
        }

        $boundary = Internal\MultiPart\Parser::parseBoundary($contentTypes);
        if (null === $boundary) {
            // No boundary provided.
            return new Form(Pipeline::fromIterable([])->getIterator());
        }

        $source = new Queue();
        $pipeline = $source->pipe();
        $options ??= new ParseOptions();

        if ($options->bodySizeLimit !== null) {
            $body->upgradeSizeLimit($options->bodySizeLimit);
        }

        EventLoop::queue(static function () use ($source, $body, $options, $boundary): void {
            try {
                Internal\MultiPart\Parser::parseIncrementally($source, $body, $options, $boundary);

                $source->complete();
            } catch (Throwable $e) {
                $source->error($e);
            }
        });

        return new Form($pipeline->getIterator());
    }
}
