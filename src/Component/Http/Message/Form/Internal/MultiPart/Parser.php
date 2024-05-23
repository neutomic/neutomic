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

namespace Neu\Component\Http\Message\Form\Internal\MultiPart;

use Amp\Http\Http1\Rfc7230;
use Amp\Http\InvalidHeaderException;
use Amp\Pipeline\Queue;
use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Body;
use Neu\Component\Http\Message\Form\Field;
use Neu\Component\Http\Message\Form\FieldInterface;
use Neu\Component\Http\Message\Form\File;
use Neu\Component\Http\Message\Form\ParseOptions;
use Neu\Component\Http\Message\RequestBodyInterface;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\StatusCode;
use Psl\Filesystem;
use Throwable;

use function count;
use function end;
use function explode;
use function in_array;
use function preg_match;
use function str_contains;
use function strlen;
use function strncmp;
use function strpos;
use function strtolower;
use function substr;
use function substr_compare;

/**
 * @internal
 *
 * @psalm-suppress MissingThrowsDocblock
 * @psalm-suppress ArgumentTypeCoercion
 */
enum Parser
{
    private const string BOUNDARY_REGEX = '#^\s*multipart/(?:form-data|mixed)(?:\s*;\s*boundary\s*=\s*("?)([^"]*)\1)?$#';
    private const string CONTENT_DISPOSITION_REGEX = '#^\s*form-data(?:\s*;\s*(?:name\s*=\s*"([^"]+)"|filename\s*=\s*"([^"]+)"))+\s*$#';
    private const string DEFAULT_CONTENT_TYPE = 'application/octet-stream';

    /**
     * Parse the boundary from the request.
     *
     * @param RequestInterface $request The HTTP request containing form data.
     * @param null|RequestBodyInterface $body The request body.
     * @param ParseOptions $options The parsing options.
     *
     * @return null|non-empty-string The boundary or null if not found.
     */
    public static function getBoundary(RequestInterface $request, null|RequestBodyInterface $body): null|string
    {
        if (null === $body) {
            // We don't have a body to parse.
            return null;
        }

        $contentTypes = $request->getHeaderLine('content-type');
        if (null === $contentTypes) {
            // No content type provided.
            return null;
        }

        if (!preg_match(self::BOUNDARY_REGEX, $contentTypes, $matches)) {
            return null;
        }

        $boundary = $matches[2];
        if ($boundary === '') {
            // No boundary provided.
            return null;
        }

        return $boundary;
    }

    /**
     * Parse the boundary from the given content type.
     */
    public static function parseBoundary(string $contentType): null|string
    {
        if (!preg_match(self::BOUNDARY_REGEX, $contentType, $matches)) {
            return null;
        }

        return $matches[2];
    }


    /**
     * Parse the entire form data into memory.
     *
     * This method loads all form data into memory at once.
     *
     * @return list<FieldInterface>
     */
    public static function parseInFull(RequestBodyInterface $body, ParseOptions $options, string $boundary): array
    {
        $fileCount = 0;
        $fields = [];

        $content = $body->getContents();

        // RFC 7578, RFC 2046 Section 5.1.1
        if (strncmp($content, "--$boundary\r\n", strlen($boundary) + 4) !== 0) {
            return [];
        }

        $entries = explode("\r\n--$boundary\r\n", $content, $options->fieldCountLimit);
        $entries[0] = substr($entries[0] ?? "", strlen($boundary) + 4);
        $entries[count($entries) - 1] = substr(end($entries), 0, -strlen($boundary) - 8);

        foreach ($entries as $entry) {
            if (($position = strpos($entry, "\r\n\r\n")) === false) {
                throw new HttpException(StatusCode::BadRequest, message: 'Invalid request body, missing headers');
            }

            try {
                $headers = Rfc7230::parseHeaderPairs(substr($entry, 0, $position + 2));
            } catch (InvalidHeaderException $e) {
                throw new HttpException(StatusCode::BadRequest, message: 'Invalid headers in body part', previous: $e);
            }

            $headerMap = [];
            foreach ($headers as [$key, $value]) {
                $headerMap[strtolower($key)][] = $value;
            }

            $entry = substr($entry, $position + 4);

            $count = preg_match(self::CONTENT_DISPOSITION_REGEX, $headerMap["content-disposition"][0] ?? "", $matches);

            if (!$count || !isset($matches[1])) {
                throw new HttpException(StatusCode::BadRequest, message: 'Missing content-disposition header within multipart form');
            }

            /** @var non-empty-string $name */
            $name = $matches[1];
            $contentType = $headerMap["content-type"][0] ?? "text/plain";

            if (isset($matches[2])) {
                if ($fileCount++ === $options->fileCountLimit) {
                    throw new HttpException(
                        statusCode: StatusCode::PayloadTooLarge,
                        message: 'The number of files in the form data exceeds the limit of ' . ((string) $options->fileCountLimit) . '.',
                    );
                }

                $filename = $matches[2];
                $extension = Filesystem\get_extension($filename);
                if (null === $extension && !$options->allowFilesWithoutExtensions) {
                    throw new HttpException(StatusCode::BadRequest, message: 'All uploaded files must have an extension.');
                }

                if (null !== $options->allowedFileExtensions && !in_array($extension, $options->allowedFileExtensions, true)) {
                    throw new HttpException(StatusCode::BadRequest, message: 'The uploaded file has an invalid extension.');
                }

                $fields[] = File::create($name, $filename, $contentType, $extension, $headerMap, Body::fromString($entry));
            } else {
                $fields[] = Field::create($name, $headerMap, Body::fromString($entry));
            }
        }

        if (str_contains($entry, "--$boundary")) {
            throw new HttpException(
                statusCode: StatusCode::PayloadTooLarge,
                message: 'The number of fields in the form data exceeds the limit of ' . ((string) $options->fieldCountLimit) . '.',
            );
        }

        return $fields;
    }

    /**
     * Parse the form data incrementally from the request body.
     *
     * @throws HttpException
     */
    public static function parseIncrementally(Queue $source, RequestBodyInterface $body, ParseOptions $options, string $boundary): void
    {
        $fieldCount = 0;
        $fileCount = 0;
        $queue = null;

        try {
            $buffer = "";

            // RFC 7578, RFC 2046 Section 5.1.1
            $boundarySeparator = "--$boundary";
            while (strlen($buffer) < strlen($boundarySeparator) + 4) {
                $chunk = $body->getChunk();

                if ($chunk === null) {
                    throw new HttpException(StatusCode::BadRequest, message: 'Request body ended unexpectedly');
                }

                $buffer .= $chunk;
            }

            $offset = strlen($boundarySeparator);
            if (strncmp($buffer, $boundarySeparator, $offset)) {
                throw new HttpException(StatusCode::BadRequest, message: 'Invalid boundary');
            }

            $boundarySeparator = "\r\n$boundarySeparator";
            while (substr_compare($buffer, "--\r\n", $offset)) {
                $offset += 2;

                $end = 0;
                while (($end = strpos($buffer, "\r\n\r\n", $offset)) === false) {
                    $chunk = $body->getChunk();

                    if ($chunk === null) {
                        throw new HttpException(StatusCode::BadRequest, message: 'Request body ended unexpectedly');
                    }

                    $buffer .= $chunk;
                }

                if ($fieldCount++ === $options->fieldCountLimit) {
                    throw new HttpException(
                        statusCode: StatusCode::PayloadTooLarge,
                        message: 'The number of fields in the form data exceeds the limit of ' . ((string) $options->fieldCountLimit) . '.',
                    );
                }

                try {
                    $headers = Rfc7230::parseHeaderPairs(substr($buffer, $offset, $end + 2 - $offset));
                } catch (InvalidHeaderException $e) {
                    throw new HttpException(StatusCode::BadRequest, message: 'Invalid headers in body part', previous: $e);
                }

                /** @var array<non-empty-string, non-empty-list<non-empty-string>> $headerMap */
                $headerMap = [];
                foreach ($headers as [$key, $value]) {
                    $headerMap[strtolower($key)][] = $value;
                }

                $contentDisposition = $headerMap["content-disposition"][0] ?? null;
                if ($contentDisposition === null) {
                    throw new HttpException(StatusCode::BadRequest, message: 'Missing content-disposition header within multipart form');
                }

                $count = preg_match(self::CONTENT_DISPOSITION_REGEX, $contentDisposition, $matches);
                if (!$count || !isset($matches[1])) {
                    throw new HttpException(StatusCode::BadRequest, message: 'Invalid content-disposition header within multipart form');
                }

                $fieldName = $matches[1];
                /** @var Queue<string> $queue */
                $queue = new Queue();
                $filename = $matches[2] ?? null;
                if (null !== $filename && $filename !== '') {
                    if ($fileCount++ === $options->fileCountLimit) {
                        throw new HttpException(
                            statusCode: StatusCode::PayloadTooLarge,
                            message: 'The number of files in the form data exceeds the limit of ' . ((string) $options->fileCountLimit) . '.',
                        );
                    }

                    $extension = Filesystem\get_extension($filename);
                    if (null === $extension && !$options->allowFilesWithoutExtensions) {
                        throw new HttpException(
                            statusCode: StatusCode::BadRequest,
                            message: 'All uploaded files must have an extension.',
                        );
                    }

                    if (null !== $options->allowedFileExtensions && !in_array($extension, $options->allowedFileExtensions, true)) {
                        throw new HttpException(
                            statusCode: StatusCode::BadRequest,
                            message: 'The uploaded file has an invalid extension.',
                        );
                    }

                    $field = File::create(
                        $fieldName,
                        $filename,
                        $headerMap["content-type"][0] ?? self::DEFAULT_CONTENT_TYPE,
                        $extension,
                        $headerMap,
                        Body::fromIterable($queue->iterate())
                    );
                } else {
                    $field = Field::create(
                        $fieldName,
                        $headerMap,
                        Body::fromIterable($queue->iterate())
                    );
                }

                $future = $source->pushAsync($field);

                $buffer = substr($buffer, $end + 4);

                while (($end = strpos($buffer, $boundarySeparator)) === false) {
                    $bufferLength = strlen($buffer);
                    $boundarySeparatorLength = strlen($boundarySeparator);
                    if ($bufferLength > $boundarySeparatorLength) {
                        $position = $bufferLength - $boundarySeparatorLength;
                        $queue->push(substr($buffer, 0, $position));
                        $buffer = substr($buffer, $position);
                    }

                    $chunk = $body->getChunk();
                    if ($chunk === null) {
                        throw new HttpException(StatusCode::BadRequest, message: 'Request body ended unexpectedly');
                    }

                    $buffer .= $chunk;
                }

                /** @var int<0, max> $end */
                $queue->push(substr($buffer, 0, $end));
                $queue->complete();
                $queue = null;
                $offset = $end + strlen($boundarySeparator);

                while (strlen($buffer) < 4) {
                    $chunk = $body->getChunk();
                    if ($chunk === null) {
                        throw new HttpException(StatusCode::BadRequest, message: 'Request body ended unexpectedly');
                    }

                    $buffer .= $chunk;
                }

                $future->await();
            }
        } catch (Throwable $e) {
            /** @var null|Queue<string> $queue */
            $queue?->error($e);

            throw $e;
        }
    }
}
