<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Internal;

use Neu\Component\Http\Message\Exception\InvalidArgumentException;

use function array_merge;
use function preg_match;
use function strtolower;

/**
 * Internal storage of message headers.
 *
 * @internal
 */
final readonly class HeaderStorage
{
    /**
     * The headers of the message.
     *
     * @var array<non-empty-string, non-empty-list<non-empty-string>>
     */
    private array $headers;

    /**
     * The header names of the message, normalized to lowercase.
     *
     * @var array<non-empty-string, non-empty-string>
     */
    private array $headerNames;

    /**
     * @param array<non-empty-string, non-empty-list<non-empty-string>> $headers
     * @param array<non-empty-string, non-empty-string> $headerNames
     */
    private function __construct(array $headers, array $headerNames)
    {
        $this->headers = $headers;
        $this->headerNames = $headerNames;
    }

    /**
     * Create a new instance from an array of headers.
     *
     * @param array<non-empty-string, non-empty-list<non-empty-string>> $headers
     *
     * @return self
     */
    public static function fromHeaders(array $headers): self
    {
        $headerNames = $validHeaders = [];

        foreach ($headers as $header => $value) {
            $value = self::filterHeaderValue($value);

            self::assertHeaderNameIsValid($header);

            $headerNames[strtolower($header)] = $header;
            $validHeaders[$header] = $value;
        }

        return new self($validHeaders, $headerNames);
    }

    /**
     * @return array<non-empty-string, non-empty-list<non-empty-string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @var non-empty-string $name
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    /**
     * @var non-empty-string $name
     *
     * @return non-empty-list<non-empty-string>|null
     */
    public function getHeader(string $name): ?array
    {
        $normalized = strtolower($name);
        if (!isset($this->headerNames[$normalized])) {
            return null;
        }

        $name = $this->headerNames[$normalized];

        return $this->headers[$name];
    }

    /**
     * @var non-empty-string $name
     *
     * @return non-empty-string|null
     */
    public function getHeaderLine(string $name): ?string
    {
        $header = $this->getHeader($name);
        if ($header === null) {
            return null;
        }

        return implode(', ', $header);
    }

    /**
     * @var non-empty-string $name
     * @var non-empty-string|non-empty-list<non-empty-string> $value
     */
    public function withHeader(string $name, array|string $value): self
    {
        self::assertHeaderNameIsValid($name);

        $normalized = strtolower($name);
        $headers = $this->headers;
        $headerNames = $this->headerNames;
        if ($this->hasHeader($name)) {
            unset($headers[$this->headerNames[$normalized]]);
        }

        $value = self::filterHeaderValue($value);

        $headerNames[$normalized] = $name;
        $headers[$name] = $value;

        return new self($headers, $headerNames);
    }

    /**
     * @var non-empty-string $name
     * @var non-empty-string|non-empty-list<non-empty-string> $value
     */
    public function withAddedHeader(string $name, array|string $value): self
    {
        self::assertHeaderNameIsValid($name);

        if (!$this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        $name = $this->headerNames[strtolower($name)];
        $headers[$name] = array_merge($this->headers[$name], self::filterHeaderValue($value));

        return new self($headers, $this->headerNames);
    }

    /**
     * @var non-empty-string $name
     */
    public function withoutHeader(string $name): self
    {
        if (!$this->hasHeader($name)) {
            return clone $this;
        }

        $normalized = strtolower($name);
        $header   = $this->headerNames[$normalized];

        $headers = $this->headers;
        $headerNames = $this->headerNames;
        unset($headers[$header], $headerNames[$normalized]);

        return new self($headers, $headerNames);
    }

    /**
     * Filter header values.
     *
     * @param non-empty-string|list<non-empty-string> $value
     *
     * @throws InvalidArgumentException
     *
     * @return non-empty-list<non-empty-string>
     */
    private static function filterHeaderValue(string|array $value): array
    {
        if (is_string($value)) {
            self::assertHeaderValueIsValid($value);
            return [$value];
        }

        if ([] === $value) {
            throw new InvalidArgumentException('Header value can not be empty');
        }

        $values = [];
        foreach ($value as $v) {
            self::assertHeaderValueIsValid($v);
            $values[] = $v;
        }

        return $values;
    }

    /**
     * Validate a header value.
     *
     * Per RFC 7230, only VISIBLE ASCII characters, spaces, and horizontal
     * tabs are allowed in values; header continuations MUST consist of
     * a single CRLF sequence followed by a space or horizontal tab.
     *
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     *
     * @param non-empty-string $value
     */
    public static function isHeaderValueValid(string $value): bool
    {
        // Empty header values are not allowed
        if ($value === '') {
            return false;
        }

        // Look for:
        // \n not preceded by \r, OR
        // \r not followed by \n, OR
        // \r\n not followed by space or horizontal tab; these are all CRLF attacks
        if (preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $value)) {
            return false;
        }

        // Non-visible, non-whitespace characters
        // 9 === horizontal tab
        // 10 === line feed
        // 13 === carriage return
        // 32-126, 128-254 === visible
        // 127 === DEL (disallowed)
        // 255 === null byte (disallowed)
        if (preg_match('/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/', $value)) {
            return false;
        }

        return true;
    }

    /**
     * Assert a header value is valid.
     *
     * @throws InvalidArgumentException for invalid values
     *
     * @psalm-assert non-empty-string $value
     */
    public static function assertHeaderValueIsValid(string $value): void
    {
        if (!self::isHeaderValueValid($value)) {
            throw new InvalidArgumentException('"' . $value . '" is not valid header value');
        }
    }

    /**
     * Assert whether a header name is valid.
     *
     * @throws InvalidArgumentException
     *
     * @see http://tools.ietf.org/html/rfc7230#section-3.2
     *
     * @psalm-assert non-empty-string $name
     */
    public static function assertHeaderNameIsValid(string $name): void
    {
        if (!preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name)) {
            throw new InvalidArgumentException('"' . $name . '" is not valid header name');
        }
    }
}
