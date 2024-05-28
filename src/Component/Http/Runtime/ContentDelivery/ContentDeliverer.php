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

namespace Neu\Component\Http\Runtime\ContentDelivery;

use Amp\File;
use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Body;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Runtime\ContentDelivery\Internal\Boundary;
use Neu\Component\Http\Runtime\ContentDelivery\Internal\ContentTypes;
use Neu\Component\Http\Runtime\Exception\FileNotFoundHttpException;
use Neu\Component\Http\Runtime\Exception\FilesystemException;
use Psl\Encoding;
use Psl\Filesystem;
use Psl\Hash;
use Psl\Iter;
use Psl\Math;
use Psl\Str;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function Amp\Http\formatDateHeader;
use function strtotime;

/**
 * The {@see ContentDeliverer} class provides a straightforward way to handle the delivering of static files in response to HTTP requests.
 *
 * It encapsulates the logic necessary to read a specified file from the filesystem, prepare it for HTTP transmission,
 * and return it as a response object. This includes support for features like HTTP range requests to enable efficient file transfers.
 *
 * This class is particularly useful in scenarios where files need to be delivered directly to clients without processing,
 * such as delivering images, documents, and other static content in a web application.
 *
 * @psalm-type Range = array{start: int, length: int}
 *
 * @psalm-suppress RedundantCondition - || true is necessary for the assert() calls to work.
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class ContentDeliverer
{
    /**
     * Chunk size for reading file content during delivery, set to 8192 bytes.
     */
    private const int READ_CHUNK_SIZE = 8192;

    /**
     * The boundary string used to separate multipart byte ranges in the response body.
     */
    private string $boundary;

    /**
     * The logger used to log messages and errors.
     */
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger = new NullLogger())
    {
        $this->logger = $logger;
        $this->boundary = Boundary::get();
    }

    /**
     * Deliver a file as an HTTP response based on the incoming HTTP request.
     *
     * This method checks the request for headers like 'Range' to provide partial content delivery if requested.
     *
     * It handles the setting of appropriate response headers such as 'Content-Type', 'Content-Length', and 'Content-Range'.
     *
     * The response object is configured based on the file's properties and the specifics of the request, allowing
     * for both full and partial content delivery depending on the client's needs.
     *
     * @param RequestInterface $request The HTTP request object, containing the client's request information.
     * @param non-empty-string $file The absolute path to the file that needs to be delivered.
     *
     * @throws FileNotFoundHttpException If the requested file does not exist or is a directory.
     * @throws FilesystemException If an error occurs while reading the file or preparing the response.
     *
     * @return ResponseInterface The response object containing the file content or the relevant HTTP error response.
     */
    public function deliver(RequestInterface $request, string $file): ResponseInterface
    {
        if (!File\exists($file)) {
            // Return null to indicate that the request should be passed to the next handler:
            assert($this->logger->debug('Requested file "{file}" does not exist.', [
                'file' => $file,
            ]) || true);

            throw FileNotFoundHttpException::create($file);
        }

        // If the file is a directory:
        if (File\isDirectory($file)) {
            // Return null to indicate that the request should be passed to the next handler:
            assert($this->logger->debug('Requested file "{file}" is a directory.', [
                'file' => $file,
            ]) || true);

            throw FileNotFoundHttpException::isDirectory($file);
        }

        try {
            /** @var null|array{size: int, mtime: int} $stats */
            $stats = File\getStatus($file);
        } catch (File\FilesystemException $e) {
            // Log the error:
            $this->logger->error('Failed to get status of requested file "{file}": {error}', [
                'file' => $file,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            // Throw an exception:
            throw new FilesystemException('Failed to get status of requested file.', 0, $e);
        }

        if ($stats === null) {
            // This is unlikely to happen, but if it does, throw an exception:
            throw new FilesystemException('Failed to get status of requested file.');
        }

        try {
            $stream = File\openFile($file, 'r');
        } catch (File\FilesystemException $e) {
            // Log the error:
            $this->logger->error('Failed to open requested file "{file}" for reading: {error}', [
                'file' => $file,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            // Throw an exception:
            throw new FilesystemException('Failed to open requested file for reading.', 0, $e);
        }

        $response = Response::fromStatusCode(StatusCode::OK)->withHeader('etag', [
            '"' . Hash\hash($file . '-' . ((string) $stats['size']) . '-' . ((string) $stats['mtime']), Hash\Algorithm::Md5) . '"',
        ]);

        $response = $this->addLastModifiedHeader($response, $stats['mtime']);
        ['response' => $response, 'range' => $range] = $this->checkPreconditions($request, $response, $stats['mtime']);
        if (null === $range) {
            // Done.
            return $response;
        }

        $contentType = $response->getHeaderLine('content-type');
        if (null === $contentType) {
            $extension = Filesystem\get_extension($file);
            if (null === $extension) {
                $contentType = 'application/octet-stream';
            } else {
                /** @var non-empty-string $contentType */
                $contentType = ContentTypes::EXTENSION_TO_CONTENT_TYPE_MAP[$extension] ?? 'application/octet-stream';
            }

            $response = $response->withHeader('content-type', $contentType);
        }

        $ranges = $this->parseRange($range, $stats['size']);
        if (null === $ranges) {
            if ($stats['size'] === 0) {
                // Some clients add a Range header to all requests to
                // limit the size of the response. If the file is empty,
                // ignore the range header and respond with a 200 rather
                // than a 416.
                $ranges = [];
            } else {
                return $response
                    ->withHeader('content-range', 'bytes */' . ((string) $stats['size']))
                    ->withStatus(StatusCode::RangeNotSatisfiable)
                ;
            }
        }

        // Sum the total size of requested byte ranges
        $rangesSumSize = Iter\reduce($ranges, static fn (int $carry, array $range): int => $carry + $range['length'], 0);
        // Check if the requested ranges exceed the file size
        if ($rangesSumSize > $stats['size']) {
            // If the sum of the ranges is greater than the file size, reset ranges to avoid processing
            // potentially harmful or nonsensical requests. This prevents inefficiencies and mitigates
            // the risk of attacks, ensuring the response handler sends the entire file safely.
            $ranges = [];
        }

        $rangesCount = Iter\count($ranges);
        if (1 === $rangesCount) {
            // RFC 7233, Section 4.1:
            // "If a single part is being transferred, the server
            // generating the 206 response MUST generate a
            // Content-Range header field, describing what range
            // of the selected representation is enclosed, and a
            // payload consisting of the range.
            // ...
            // A server MUST NOT generate a multipart response to
            // a request for a single range, since a client that
            // does not request multiple parts might not support
            // multipart responses."
            $range = $ranges[0];
            $response = $response
                ->withStatus(StatusCode::PartialContent)
                ->withHeader('content-length', (string) $range['length'])
                ->withHeader('content-range', $this->getContentRangeHeaderValue($range, $stats['size']))
            ;

            $callback =
                /**
                 * @return iterable<string>
                 */
                function () use ($range, $stream): iterable {
                    yield from $this->streamRangeFromFile($stream, $range);
                }
            ;
        } elseif ($rangesCount > 1) {
            $response = $response
                ->withStatus(StatusCode::PartialContent)
                ->withHeader('content-length', (string) $this->calculateMultipartSize($ranges, $contentType, $stats['size']))
                ->withHeader('content-type', 'multipart/byteranges; boundary=' . $this->boundary);

            $callback =
                /**
                 * @return iterable<string>
                 */
                function () use ($ranges, $stream, $contentType, $stats): iterable {
                    $lastRange = null;
                    foreach ($ranges as $range) {
                        if (null !== $lastRange) {
                            yield "\r\n--" . $this->boundary . "\r\n";
                        } else {
                            yield "--" . $this->boundary . "\r\n";
                        }

                        foreach ($this->getRangeHeaders($range, $contentType, $stats['size']) as $header => $value) {
                            yield $header . ': ' . $value . "\r\n";
                        }

                        yield "\r\n";

                        yield from $this->streamRangeFromFile($stream, $range);

                        $lastRange = $range;
                    }

                    yield "\r\n--" . $this->boundary . "--\r\n";
                }
            ;
        } else {
            $response = $response->withHeader('content-length', (string) $stats['size']);

            $callback =
                /**
                 * @return iterable<string>
                 */
                static function () use ($stream): iterable {
                    while (null !== $chunk = $stream->read(length: self::READ_CHUNK_SIZE)) {
                        yield $chunk;
                    }
                }
            ;
        }

        $response = $response->withHeader('accept-ranges', 'bytes');

        if ($request->getMethod() !== Method::Head) {
            $response = $response->withBody(Body::fromIterable($callback()));
        }

        return $response;
    }

    /**
     * Evaluates request headers to determine if the requested modifications and ranges are valid.
     *
     * @return array{response: ResponseInterface, range: ?string}
     */
    private function checkPreconditions(RequestInterface $request, ResponseInterface $response, int $modificationTime): array
    {
        $check = $this->checkIfMatch($request, $response);
        if (null === $check) {
            $check = $this->isUnmodifiedSince($request, $modificationTime);
        }

        if (false === $check) {
            $response = $response->withStatus(StatusCode::PreconditionFailed);

            return ['response' => $response, 'range' => null];
        }

        $check = $this->checkIfNoneMatch($request, $response);
        if (false === $check) {
            $method = $request->getMethod();
            if ($method === Method::Get || $method === Method::Head) {
                $response = $this->returnNotModifiedResponse($response);
            } else {
                $response = $response->withStatus(StatusCode::PreconditionFailed);
            }

            return ['response' => $response, 'range' => null];
        }

        if (null === $check && false === $this->isModifiedSince($request, $modificationTime)) {
            $response = $this->returnNotModifiedResponse($response);

            return ['response' => $response, 'range' => null];
        }

        $range = $request->getHeaderLine('range') ?? '';
        if ('' !== $range && false === $this->checkIfRange($request, $response, $modificationTime)) {
            $range = '';
        }

        return ['response' => $response, 'range' => $range];
    }

    /**
     * Yields the specified byte range from the file, reading chunks according to the defined chunk size option.
     *
     * @param File\File $stream
     * @param Range $range
     *
     * @return iterable<string>
     */
    private function streamRangeFromFile(File\File $stream, array $range): iterable
    {
        $stream->seek($range['start']);
        $remaining = $range['length'];
        while ($remaining > 0) {
            $length = Math\minva($remaining, self::READ_CHUNK_SIZE);
            $chunk = $stream->read(length: $length);

            if (null !== $chunk) {
                yield $chunk;
            } else {
                return;
            }

            $size = Str\Byte\length($chunk);
            $remaining -= $size;
        }
    }

    /**
     * Parses the 'Range' header to extract byte ranges, validating and adjusting them against the file size.
     *
     * @return null|list<Range>
     */
    private function parseRange(string $headerValue, int $size): null|array
    {
        if ('' === $headerValue) {
            return [];
        }

        $bytes = 'bytes=';
        if (!Str\Byte\starts_with($headerValue, $bytes)) {
            // Invalid range.
            throw new HttpException(StatusCode::RangeNotSatisfiable);
        }

        $ranges = [];
        $noOverlap = false;
        foreach (Str\Byte\split(Str\Byte\strip_prefix($headerValue, $bytes), ',') as $part) {
            $part = Str\Byte\trim($part);
            if ('' === $part) {
                continue;
            }

            if (!Str\Byte\contains($part, '-')) {
                // Invalid range.
                throw new HttpException(StatusCode::RangeNotSatisfiable);
            }

            /** @var array{0: string, 1: string} $parts */
            $parts = Str\Byte\split($part, '-', limit: 2);
            $start = Str\Byte\trim($parts[0]);
            $end = Str\Byte\trim($parts[1]);
            if ('' === $start) {
                // If no start is specified, end specifies the
                // range start relative to the end of the file,
                // and we are dealing with <suffix-length>
                // which has to be a non-negative integer as per
                // RFC 7233 Section 2.1 "Byte-Ranges".
                if ('' === $end || $end[0] === '-') {
                    // Invalid range.
                    throw new HttpException(StatusCode::RangeNotSatisfiable);
                }

                $end = Str\to_int($end);
                if (null === $end || $end < 0) {
                    // Invalid range.
                    throw new HttpException(StatusCode::RangeNotSatisfiable);
                }

                if ($end > $size) {
                    $end = $size;
                }

                $start = $size - $end;
                $length = $size - $start;
            } else {
                $start = Str\to_int($start);
                if (null === $start) {
                    // Invalid range.
                    throw new HttpException(StatusCode::RangeNotSatisfiable);
                }

                if ($start >= $size) {
                    // If the range begins after the size of the content,
                    // then it does not overlap.
                    $noOverlap = true;
                    continue;
                }

                if ('' === $end) {
                    // If no end is specified, range extends to end of the file.
                    $length = $size - $start;
                } else {
                    $end = Str\to_int($end);
                    if (null === $end || $start > $end) {
                        // Invalid range.
                        throw new HttpException(StatusCode::RangeNotSatisfiable);
                    }

                    if ($end >= $size) {
                        $end = $size - 1;
                    }

                    $length = $end - $start + 1;
                }
            }

            $ranges[] = ['start' => $start, 'length' => $length];
        }

        if ($noOverlap && [] === $ranges) {
            return null;
        }

        return $ranges;
    }

    /**
     * Formats the 'Content-Range' header value for a given byte range.
     *
     * @param Range $range
     *
     * @return non-empty-string
     */
    private function getContentRangeHeaderValue(array $range, int $size): string
    {
        return 'bytes ' . ((string) $range['start']) . '-' . ((string) ($range['start'] + $range['length'] - 1)) . '/' . ((string) $size);
    }

    /**
     * Constructs headers for a single byte range response including 'Content-Type' and 'Content-Range'.
     *
     * @param Range $range
     * @param non-empty-string $contentType
     *
     * @return array{content-type: non-empty-string, content-range: non-empty-string}
     */
    private function getRangeHeaders(array $range, string $contentType, int $size): array
    {
        return [
            'content-type' => $contentType,
            'content-range' => $this->getContentRangeHeaderValue($range, $size),
        ];
    }

    /**
     * Checks if the file was not modified since the date specified in the 'If-Unmodified-Since' header.
     *
     * @return null|bool True if the file was not modified since the specified date, false if it was modified,
     *                   and null if the header is not present or the date is invalid.
     */
    private function isUnmodifiedSince(RequestInterface $request, int $modificationTime): null|bool
    {
        $ifUnmodifiedSince = $request->getHeaderLine('if-unmodified-since');
        if (null === $ifUnmodifiedSince || $modificationTime <= 0) {
            return null;
        }

        $ifUnmodifiedSince = strtotime($ifUnmodifiedSince);
        if (false === $ifUnmodifiedSince) {
            return null;
        }

        return $ifUnmodifiedSince >= $modificationTime;
    }

    /**
     * Checks if the file was modified since the date specified in the 'If-Modified-Since' header.
     *
     * @return null|bool True if the file was modified since the specified date, false if it was not modified,
     *                   and null if the request method is not GET or HEAD, the header is not present, or the date is invalid.
     */
    private function isModifiedSince(RequestInterface $request, int $modificationTime): null|bool
    {
        $method = $request->getMethod();
        if ($method !== Method::Get && $method !== Method::Head) {
            return null;
        }

        $ifModifiedSince = $request->getHeaderLine('if-modified-since');
        if (null === $ifModifiedSince || $modificationTime <= 0) {
            return null;
        }

        $ifModifiedSince = strtotime($ifModifiedSince);
        if (false === $ifModifiedSince) {
            return null;
        }

        return $ifModifiedSince < $modificationTime;
    }

    /**
     * Adds the 'Last-Modified' header to the response if the modification time is greater than zero.
     */
    private function addLastModifiedHeader(ResponseInterface $response, int $modificationTime): ResponseInterface
    {
        if ($modificationTime > 0) {
            /** @var non-empty-string $lastModified */
            $lastModified = formatDateHeader($modificationTime);
            $response = $response->withHeader('last-modified', $lastModified);
        }

        return $response;
    }

    /**
     * Returns a 304 (Not Modified) response.
     *
     * This method removes headers that are not relevant for a 304 response, such as 'Content-Type', 'Content-Length',
     * 'Content-Encoding', and 'Last-Modified'. It also sets the status code to 304 (Not Modified).
     */
    private function returnNotModifiedResponse(ResponseInterface $response): ResponseInterface
    {
        $response = $response
            ->withoutHeader('content-type')
            ->withoutHeader('content-length')
            ->withoutHeader('content-encoding')
        ;

        if ($response->hasHeader('etag')) {
            $response = $response->withoutHeader('last-modified');
        }

        return $response->withStatus(StatusCode::NotModified);
    }

    /**
     * Checks if the 'If-Match' header matches the 'ETag' header of the response.
     *
     * @return null|bool True if the ETag matches, false if it does not, and null if the header is not present or invalid.
     */
    private function checkIfMatch(RequestInterface $request, ResponseInterface $response): null|bool
    {
        $ifMatches = $request->getHeaderLine('if-match');
        if (null === $ifMatches) {
            return null;
        }

        $responseEtag = $response->getHeaderLine('etag');

        do {
            $ifMatches = Str\Byte\trim($ifMatches);
            if ('' === $ifMatches) {
                break;
            }

            if (Str\Byte\starts_with($ifMatches, ',')) {
                $ifMatches = Str\Byte\slice($ifMatches, 1);
                continue;
            }

            if (Str\Byte\starts_with($ifMatches, '*')) {
                return true;
            }

            ['etag' => $etag, 'remain' => $remain] = $this->parseEtag($ifMatches);
            if ('' === $etag) {
                break;
            }

            if (null !== $responseEtag && $this->isStrongEtagMatch($etag, $responseEtag)) {
                return true;
            }

            $ifMatches = $remain;
        } while (true);

        return false;
    }

    /**
     * Checks if the 'If-Range' header matches the 'ETag' or 'Last-Modified' header of the response.
     *
     * @return null|bool True if the ETag or Last-Modified matches, false if it does not, and null if the header is not present or invalid.
     */
    private function checkIfRange(RequestInterface $request, ResponseInterface $response, int $modificationTime): null|bool
    {
        $method = $request->getMethod();
        if ($method !== Method::Get && $method !== Method::Head) {
            return null;
        }

        $ifRange = $request->getHeaderLine('if-range');
        if (null === $ifRange) {
            return null;
        }

        ['etag' => $etag] = $this->parseEtag($ifRange);
        if ('' !== $etag) {
            $responseEtag = $response->getHeaderLine('etag');

            return null !== $responseEtag && !$this->isStrongEtagMatch($etag, $responseEtag);
        }

        if ($modificationTime <= 0) {
            return false;
        }

        $ifRange = strtotime($ifRange);
        if (false === $ifRange) {
            return false;
        }

        return $ifRange === $modificationTime;
    }

    /**
     * Checks if the 'If-None-Match' header matches the 'ETag' header of the response.
     *
     * @return null|bool True if the ETag does not match, false if it does, and null if the header is not present or invalid.
     */
    private function checkIfNoneMatch(RequestInterface $request, ResponseInterface $response): null|bool
    {
        $ifNoneMatch = $request->getHeaderLine('if-none-match');
        if (null === $ifNoneMatch) {
            return null;
        }

        $responseEtag = $response->getHeaderLine('etag');

        do {
            $ifNoneMatch = Str\Byte\trim($ifNoneMatch);
            if ('' === $ifNoneMatch) {
                break;
            }

            if (Str\Byte\starts_with($ifNoneMatch, ',')) {
                $ifNoneMatch = Str\Byte\slice($ifNoneMatch, 1);
                continue;
            }

            if (Str\Byte\starts_with($ifNoneMatch, '*')) {
                return false;
            }

            ['etag' => $etag, 'remain' => $remain] = $this->parseEtag($ifNoneMatch);
            if ('' === $etag) {
                break;
            }

            if (null !== $responseEtag && $this->isWeakEtagMatch($etag, $responseEtag)) {
                return false;
            }

            $ifNoneMatch = $remain;
        } while (true);

        return true;
    }

    /**
     * Parses an ETag from a header value and separates it from any remaining header string.
     *
     * @return array{etag: string, remain: string}
     */
    private function parseEtag(string $etag): array
    {
        $etag = Str\Byte\trim($etag);
        $start = 0;
        if (Str\Byte\starts_with($etag, 'W/')) {
            $start = 2;
        }

        if (Str\Byte\length(Str\Byte\slice($etag, $start)) < 2 || $etag[$start] !== '"') {
            return ['etag' => '', 'remain' => ''];
        }

        for ($i = $start + 1, $length = Str\Byte\length($etag); $i < $length; $i++) {
            $char = $etag[$i];
            if ($char === '"') {
                return ['etag' => Str\Byte\slice($etag, 0, $i + 1), 'remain' => Str\Byte\slice($etag, $i + 1)];
            }

            $ord = Str\Byte\ord($char);
            if ($ord === 0x21 || ($ord >= 0x23 && $ord <= 0x7E) || $ord >= 0x80) {
                // Character values allowed in ETags.
                continue;
            }

            // Invalid character in ETag.
            break;
        }

        return ['etag' => '', 'remain' => ''];
    }

    /**
     * Checks for a strong match between two ETags, considering exact string comparison.
     *
     * Assumes that $a and $b are valid ETags.
     *
     * @psalm-assert-if-true non-empty-string $a
     * @psalm-assert-if-true non-empty-string $b
     */
    private function isStrongEtagMatch(string $a, string $b): bool
    {
        return $a === $b && $a !== '' && $a[0] === '"';
    }

    /**
     * Checks for a weak match between two ETags, ignoring any 'W/' prefix used to denote weak comparison.
     *
     * Assumes that $a and $b are valid ETags.
     */
    private function isWeakEtagMatch(string $a, string $b): bool
    {
        return Str\Byte\strip_prefix($a, 'W/') === Str\Byte\strip_prefix($b, 'W/');
    }

    /**
     * Calculate the size of the response body for a multipart/byteranges response without reading the file.
     *
     * @param list<Range> $ranges
     * @param non-empty-string $contentType
     * @param int $size
     *
     * @return int<0, max>
     */
    private function calculateMultipartSize(array $ranges, mixed $contentType, int $size): int
    {
        $totalLength = 0;
        $lastRange = null;
        foreach ($ranges as $range) {
            // If this is not the first range, we need to add the boundary and the line break.
            // 2 = "--", 4 = "\r\n--".
            $prefix = $lastRange ? 4 : 2;
            $totalLength += $prefix + Str\Byte\length($this->boundary) + 2;

            foreach ($this->getRangeHeaders($range, $contentType, $size) as $header => $value) {
                $totalLength += Str\Byte\length($header);
                $totalLength += 2; // ": "
                $totalLength += Str\Byte\length($value);
                $totalLength += 2; // "\r\n"
            }

            $totalLength += 2; // "\r\n"
            $totalLength += $range['length'];
            $lastRange = $range;
        }

        // Final boundary:
        // "\r\n--" + boundary + "--\r\n"
        $totalLength += 4 + Str\Byte\length($this->boundary) + 4;

        /** @var int<0, max> $totalLength */
        return $totalLength;
    }
}
