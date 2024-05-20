<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Form\Internal\MultiPart;

use Amp\Http\Http1\Rfc7230;
use Amp\Http\InvalidHeaderException;
use Amp\Pipeline\Queue;
use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Body;
use Neu\Component\Http\Message\Form\Field;
use Neu\Component\Http\Message\Form\File;
use Neu\Component\Http\Message\Form\ParseOptions;
use Neu\Component\Http\Message\RequestBodyInterface;
use Neu\Component\Http\Message\StatusCode;
use Psl\Filesystem;
use Throwable;

use function in_array;
use function preg_match;
use function strlen;
use function strncmp;
use function strpos;
use function strtolower;
use function substr;
use function substr_compare;

final readonly class Parser
{
    private const string BOUNDARY_REGEX = '#^\s*multipart/(?:form-data|mixed)(?:\s*;\s*boundary\s*=\s*("?)([^"]*)\1)?$#';
    private const string CONTENT_DISPOSITION_REGEX = '#^\s*form-data(?:\s*;\s*(?:name\s*=\s*"([^"]+)"|filename\s*=\s*"([^"]+)"))+\s*$#';
    private const string DEFAULT_CONTENT_TYPE = 'application/octet-stream';

    /**
     * Parse the boundary from the given content type.
     */
    public static function parseBoundary(string $contentType): ?string
    {
        if (!preg_match(self::BOUNDARY_REGEX, $contentType, $matches)) {
            return null;
        }

        return $matches[2];
    }

    /**
     * Parse the form data incrementally from the request body.
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
                $buffer .= $chunk = $body->getChunk();

                if ($chunk === null) {
                    throw new HttpException(StatusCode::BadRequest, message: 'Request body ended unexpectedly');
                }
            }

            $offset = strlen($boundarySeparator);
            if (strncmp($buffer, $boundarySeparator, $offset)) {
                throw new HttpException(StatusCode::BadRequest, message: 'Invalid boundary');
            }

            $boundarySeparator = "\r\n$boundarySeparator";
            while (substr_compare($buffer, "--\r\n", $offset)) {
                $offset += 2;

                while (($end = strpos($buffer, "\r\n\r\n", $offset)) === false) {
                    $buffer .= $chunk = $body->getChunk();

                    if ($chunk === null) {
                        throw new HttpException(StatusCode::BadRequest, message: 'Request body ended unexpectedly');
                    }
                }

                if ($fieldCount++ === $options->fieldCountLimit) {
                    throw new HttpException(StatusCode::PayloadTooLarge, message: 'Maximum number of fields exceeded');
                }

                try {
                    $headers = Rfc7230::parseHeaderPairs(substr($buffer, $offset, $end + 2 - $offset));
                } catch (InvalidHeaderException $e) {
                    throw new HttpException(StatusCode::BadRequest, message: 'Invalid headers in body part', previous: $e);
                }

                /** @var array<non-empty-string, non-empty-list<non-empty-string>> $headerMap */
                $headerMap = [];
                /** @var array{0: non-empty-string, 1: non-empty-string} $matches */
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
                $queue = new Queue();
                $filename = $matches[2] ?? null;
                if (null !== $filename) {
                    if ($fileCount++ === $options->fileCountLimit) {
                        throw new HttpException(StatusCode::PayloadTooLarge, message: 'Maximum number of files exceeded');
                    }

                    $extension = Filesystem\get_extension($filename);
                    if (null === $extension && !$options->allowFilesWithoutExtensions) {
                        throw new HttpException(StatusCode::BadRequest, message: 'Invalid file extension');
                    }

                    if (null !== $options->allowedFileExtensions && !in_array($extension, $options->allowedFileExtensions, true)) {
                        throw new HttpException(StatusCode::BadRequest, message: 'Invalid file extension');
                    }

                    $field = File::create(
                        $fieldName,
                        $filename,
                        $headerMap["content-type"][0] ?? self::DEFAULT_CONTENT_TYPE,
                        $extension,
                        $headerMap,
                        Body::fromIterable($queue->pipe())
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

                    $buffer .= $chunk = $body->getChunk();

                    if ($chunk === null) {
                        throw new HttpException(StatusCode::BadRequest, message: 'Request body ended unexpectedly');
                    }
                }

                $queue->push(substr($buffer, 0, $end));
                $queue->complete();
                $queue = null;
                $offset = $end + strlen($boundarySeparator);

                while (strlen($buffer) < 4) {
                    $buffer .= $chunk = $body->getChunk();

                    if ($chunk === null) {
                        throw new HttpException(StatusCode::BadRequest, message: 'Request body ended unexpectedly');
                    }
                }

                $future->await();
            }
        } catch (Throwable $e) {
            $queue?->error($e);

            throw $e;
        }
    }
}
