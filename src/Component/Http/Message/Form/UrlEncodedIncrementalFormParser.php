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
use Neu\Component\Http\Message\RequestInterface;
use Revolt\EventLoop;
use Throwable;

/**
 * Parses URL-encoded form data from HTTP requests.
 */
final readonly class UrlEncodedIncrementalFormParser implements IncrementalFormParserInterface
{
    public const string CONTENT_TYPE = 'application/x-www-form-urlencoded';

    /**
     * Parses the form data from the given HTTP request.
     *
     * @param RequestInterface $request The HTTP request containing URL-encoded form data.
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
        if (null === $contentTypes || !str_starts_with($contentTypes, self::CONTENT_TYPE)) {
            return new Form(Pipeline::fromIterable([])->getIterator());
        }

        /** @var Queue<FieldInterface> $source */
        $source = new Queue();
        $pipeline = $source->pipe();
        $options ??= new ParseOptions();

        if ($options->bodySizeLimit !== null) {
            $body->upgradeSizeLimit($options->bodySizeLimit);
        }

        EventLoop::queue(static function () use ($source, $body, $options): void {
            try {
                Internal\UrlEncoded\Parser::parseIncrementally($source, $body, $options);

                $source->complete();
            } catch (Throwable $e) {
                $source->error($e);
            }
        });

        return new Form($pipeline->getIterator());
    }
}
