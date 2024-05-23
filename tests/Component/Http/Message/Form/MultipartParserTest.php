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

namespace Component\Http\Message\Form;

use Generator;
use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Form\FileInterface;
use Neu\Component\Http\Message\Form\MultipartParser;
use Neu\Component\Http\Message\Form\ParseOptions;
use Neu\Component\Http\Message\Form\Parser;
use Neu\Component\Http\Message\Form\ParserInterface;
use Neu\Component\Http\Message\Form\StreamedParserInterface;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\Request;
use Neu\Component\Http\Message\RequestBody;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress MissingThrowsDocblock
 */
final class MultipartParserTest extends TestCase
{
    private const string BOUNDARY = '---------------------------97462980627483989762330262428';

    /**
     * @return Generator<int, list<ParserInterface>, mixed, void>
     */
    public static function getParser(): Generator
    {
        yield [new MultipartParser()];
        yield [new Parser()];
    }

    /**
     * @return Generator<int, list<StreamedParserInterface>, mixed, void>
     */
    public static function getStreamingParser(): Generator
    {
        yield [new MultipartParser()];
        yield [new Parser()];
    }

    #[DataProvider('getParser')]
    public function testParseNoContentType(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');

        $form = $parser->parse($request);

        static::assertSame([], $form->getFields());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamNoContentType(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()?->getContents();
        }

        static::assertSame([], $fields);
    }

    #[DataProvider('getParser')]
    public function testParseNoBody(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);

        $form = $parser->parse($request);

        static::assertSame([], $form->getFields());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamNoBody(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()?->getContents();
        }

        static::assertSame([], $fields);
    }

    #[DataProvider('getParser')]
    public function testParseEmptyFormData(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString("--" . self::BOUNDARY . "--\r\n"));

        $form = $parser->parse($request);

        static::assertSame([], $form->getFields());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamEmptyFormData(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString("--" . self::BOUNDARY . "--\r\n"));

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()?->getContents();
        }

        static::assertSame([], $fields);
    }

    #[DataProvider('getParser')]
    public function testParseFieldWithoutValue(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"b\"\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parse($request);

        static::assertCount(2, $form->getFields());
        static::assertSame('', $form->getFirstFieldByName('a')?->getBody()?->getContents());
        static::assertSame('bravo', $form->getFirstFieldByName('b')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamFieldWithoutValue(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"b\"\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()?->getContents();
        }

        static::assertSame([
            'a' => '',
            'b' => 'bravo'
        ], $fields);
    }

    #[DataProvider('getParser')]
    public function testParseURLEncodedCharacters(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"na%20me\"\r\n\r\n" .
            "value%20with%20spaces\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"name2\"\r\n\r\n" .
            "val%40ue2\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parse($request);

        static::assertCount(2, $form->getFields());
        static::assertSame('value%20with%20spaces', $form->getFirstFieldByName('na%20me')?->getBody()?->getContents());
        static::assertSame('val%40ue2', $form->getFirstFieldByName('name2')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamURLEncodedCharacters(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"na%20me\"\r\n\r\n" .
            "value%20with%20spaces\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"name2\"\r\n\r\n" .
            "val%40ue2\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()?->getContents();
        }

        static::assertSame([
            'na%20me' => 'value%20with%20spaces',
            'name2' => 'val%40ue2'
        ], $fields);
    }

    #[DataProvider('getParser')]
    public function testParseMultipleConsecutiveBoundaries(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"a\"\r\n\r\n";
            yield "alpha\r\n";
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"b\"\r\n\r\n";
            yield "bravo\r\n";
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"c\"\r\n\r\n";
            yield "charlie\r\n";
            yield "--" . self::BOUNDARY . "--\r\n";
        })()));

        $form = $parser->parse($request);

        static::assertCount(3, $form->getFields());
        static::assertSame('alpha', $form->getFirstFieldByName('a')?->getBody()?->getContents());
        static::assertSame('bravo', $form->getFirstFieldByName('b')?->getBody()?->getContents());
        static::assertSame('charlie', $form->getFirstFieldByName('c')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamMultipleConsecutiveBoundaries(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"a\"\r\n\r\n";
            yield "alpha\r\n";
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"b\"\r\n\r\n";
            yield "bravo\r\n";
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"c\"\r\n\r\n";
            yield "charlie\r\n";
            yield "--" . self::BOUNDARY . "--\r\n";
        })()));

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()?->getContents();
        }

        static::assertSame([
            'a' => 'alpha',
            'b' => 'bravo',
            'c' => 'charlie'
        ], $fields);
    }

    #[DataProvider('getParser')]
    public function testParseFieldWithoutContentDisposition(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "X-Header: value\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Missing content-disposition header within multipart form');

        $parser->parse($request);
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamFieldWithoutContentDisposition(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "X-Header: value\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Missing content-disposition header within multipart form');

        $form = $parser->parseStreamed($request);
        // iterate over the fields to trigger the exception
        foreach ($form->getFields() as $field) {
            $field->getBody()?->getContents();
        }
    }

    #[DataProvider('getParser')]
    public function testParseSingleFieldWithValue(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parse($request);

        static::assertCount(1, $form->getFields());
        static::assertSame('alpha', $form->getFirstFieldByName('a')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamSingleFieldWithValue(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()?->getContents();
        }

        static::assertSame([
            'a' => 'alpha'
        ], $fields);
    }

    #[DataProvider('getParser')]
    public function testParseFileField(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"file\"; filename=\"file.txt\"\r\n" .
            "Content-Type: text/plain\r\n\r\n" .
            "Hello, World!\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parse($request);

        static::assertCount(1, $form->getFields());
        static::assertCount(1, $form->getFiles());

        $file = $form->getFirstFieldByName('file');

        static::assertInstanceOf(FileInterface::class, $file);

        static::assertSame('file.txt', $file->getFilename());
        static::assertSame('text/plain', $file->getMimeType());
        static::assertSame('txt', $file->getExtension());
        static::assertSame('Hello, World!', $file->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamFileField(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"file\"; filename=\"file.txt\"\r\n" .
            "Content-Type: text/plain\r\n\r\n" .
            "Hello, World!\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parseStreamed($request);

        foreach ($form->getFields() as $field) {
            static::assertInstanceOf(FileInterface::class, $field);

            static::assertSame('file.txt', $field->getFilename());
            static::assertSame('text/plain', $field->getMimeType());
            static::assertSame('txt', $field->getExtension());
            static::assertSame('Hello, World!', $field->getBody()?->getContents());
        }
    }

    #[DataProvider('getParser')]
    public function testHeadersAreParsedCorrectly(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"file\"; filename=\"file.txt\"\r\n" .
            "Content-Type: text/plain\r\n" .
            "X-Header: value\r\n\r\n" .
            "Hello, World!\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parse($request);

        $field = $form->getFirstFieldByName('file');

        static::assertInstanceOf(FileInterface::class, $field);

        static::assertSame('file.txt', $field->getFilename());
        static::assertSame('text/plain', $field->getMimeType());
        static::assertSame('Hello, World!', $field->getBody()?->getContents());
        static::assertSame(['value'], $field->getHeader('X-Header'));
        static::assertSame(['text/plain'], $field->getHeader('Content-Type'));
        static::assertSame(['form-data; name="file"; filename="file.txt"'], $field->getHeader('Content-Disposition'));
    }

    #[DataProvider('getStreamingParser')]
    public function testHeadersAreStreamParsedCorrectly(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"file\"; filename=\"file.txt\"\r\n" .
            "Content-Type: text/plain\r\n" .
            "X-Header: value\r\n\r\n" .
            "Hello, World!\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parseStreamed($request);

        $fieldCount = 0;
        foreach ($form->getFields() as $field) {
            $fieldCount++;

            static::assertInstanceOf(FileInterface::class, $field);

            static::assertSame('file.txt', $field->getFilename());
            static::assertSame('text/plain', $field->getMimeType());
            static::assertSame('Hello, World!', $field->getBody()?->getContents());
            static::assertSame(['value'], $field->getHeader('X-Header'));
            static::assertSame(['text/plain'], $field->getHeader('Content-Type'));
            static::assertSame(['form-data; name="file"; filename="file.txt"'], $field->getHeader('Content-Disposition'));
        }

        static::assertSame(1, $fieldCount);
    }

    #[DataProvider('getParser')]
    public function testParseFieldCountLimitOption(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"b\"\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"c\"\r\n\r\n" .
            "charlie\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"d\"\r\n\r\n" .
            "delta\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"e\"\r\n\r\n" .
            "echo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The number of fields in the form data exceeds the limit of 3.');

        $parser->parse($request, ParseOptions::fromFieldCountLimit(3));
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFieldCountLimitOption(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"b\"\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"c\"\r\n\r\n" .
            "charlie\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"d\"\r\n\r\n" .
            "delta\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"e\"\r\n\r\n" .
            "echo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The number of fields in the form data exceeds the limit of 3.');

        $fields = $parser->parseStreamed($request, ParseOptions::fromFieldCountLimit(3));
        // we need to iterate over the fields to trigger the exception
        foreach ($fields->getFields() as $field) {
            $field->getBody()?->getContents();
        }
    }

    #[DataProvider('getParser')]
    public function testParseFieldCountLimitOptionOfZero(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"b\"\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"c\"\r\n\r\n" .
            "charlie\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"d\"\r\n\r\n" .
            "delta\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"e\"\r\n\r\n" .
            "echo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The number of fields in the form data exceeds the limit of 0.');

        $parser->parse($request, ParseOptions::fromFieldCountLimit(0));
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFieldCountLimitOptionOfZero(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"b\"\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"c\"\r\n\r\n" .
            "charlie\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"d\"\r\n\r\n" .
            "delta\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"e\"\r\n\r\n" .
            "echo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The number of fields in the form data exceeds the limit of 0.');

        $fields = $parser->parseStreamed($request, ParseOptions::fromFieldCountLimit(0));
        // we need to iterate over the fields to trigger the exception
        foreach ($fields->getFields() as $field) {
            $field->getBody()?->getContents();
        }
    }

    #[DataProvider('getParser')]
    public function testParseFieldCountLimitAboveRequestFieldsCount(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"b\"\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"c\"\r\n\r\n" .
            "charlie\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"d\"\r\n\r\n" .
            "delta\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"e\"\r\n\r\n" .
            "echo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parse($request, ParseOptions::fromFieldCountLimit(10));

        static::assertCount(5, $form->getFields());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFieldCountLimitAboveRequestFieldsCount(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"b\"\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"c\"\r\n\r\n" .
            "charlie\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"d\"\r\n\r\n" .
            "delta\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"e\"\r\n\r\n" .
            "echo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $fields = $parser->parseStreamed($request, ParseOptions::fromFieldCountLimit(10));
        $fieldCount = 0;
        foreach ($fields->getFields() as $field) {
            $fieldCount++;
            $field->getBody()?->getContents();
        }

        static::assertSame(5, $fieldCount);
    }

    #[DataProvider('getParser')]
    public function testParseFieldCountLimitAtRequestFieldsCount(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"b\"\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"c\"\r\n\r\n" .
            "charlie\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"d\"\r\n\r\n" .
            "delta\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"e\"\r\n\r\n" .
            "echo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $form = $parser->parse($request, ParseOptions::fromFieldCountLimit(5));

        static::assertCount(5, $form->getFields());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFieldCountLimitAtRequestFieldsCount(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"b\"\r\n\r\n" .
            "bravo\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"c\"\r\n\r\n" .
            "charlie\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"d\"\r\n\r\n" .
            "delta\r\n" .
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"e\"\r\n\r\n" .
            "echo\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $fields = $parser->parseStreamed($request, ParseOptions::fromFieldCountLimit(5));
        $fieldCount = 0;
        foreach ($fields->getFields() as $field) {
            $fieldCount++;
            $field->getBody()?->getContents();
        }

        static::assertSame(5, $fieldCount);
    }

    #[DataProvider('getParser')]
    public function testParseFileCountLimitOption(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            for ($i = 0; $i < 4; $i++) {
                yield "Content-Disposition: form-data; name=\"file{$i}\"; filename=\"file{$i}.txt\"\r\n";
                yield "Content-Type: text/plain\r\n\r\n";
                yield "Hello, World!\r\n";
                yield "--" . self::BOUNDARY . ($i === 3 ? "--\r\n" : "\r\n");
            }
        })()));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The number of files in the form data exceeds the limit of 3.');

        $parser->parse($request, ParseOptions::create()->withFileCountLimit(3));
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFileCountLimitOption(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            for ($i = 0; $i < 4; $i++) {
                yield "Content-Disposition: form-data; name=\"file{$i}\"; filename=\"file{$i}.txt\"\r\n";
                yield "Content-Type: text/plain\r\n\r\n";
                yield "Hello, World!\r\n";
                yield "--" . self::BOUNDARY . ($i === 3 ? "--\r\n" : "\r\n");
            }
        })()));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The number of files in the form data exceeds the limit of 3.');

        $fields = $parser->parseStreamed($request, ParseOptions::create()->withFileCountLimit(3));
        // we need to iterate over the fields to trigger the exception
        foreach ($fields->getFields() as $field) {
            $field->getBody()?->getContents();
        }
    }

    #[DataProvider('getParser')]
    public function testParseFileCountLimitOptionOfZero(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            for ($i = 0; $i < 4; $i++) {
                yield "Content-Disposition: form-data; name=\"file{$i}\"; filename=\"file{$i}.txt\"\r\n";
                yield "Content-Type: text/plain\r\n\r\n";
                yield "Hello, World!\r\n";
                yield "--" . self::BOUNDARY . ($i === 3 ? "--\r\n" : "\r\n");
            }
        })()));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The number of files in the form data exceeds the limit of 0.');

        $parser->parse($request, ParseOptions::create()->withFileCountLimit(0));
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFileCountLimitOptionOfZero(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            for ($i = 0; $i < 4; $i++) {
                yield "Content-Disposition: form-data; name=\"file{$i}\"; filename=\"file{$i}.txt\"\r\n";
                yield "Content-Type: text/plain\r\n\r\n";
                yield "Hello, World!\r\n";
                yield "--" . self::BOUNDARY . ($i === 3 ? "--\r\n" : "\r\n");
            }
        })()));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The number of files in the form data exceeds the limit of 0.');

        $fields = $parser->parseStreamed($request, ParseOptions::create()->withFileCountLimit(0));
        // we need to iterate over the fields to trigger the exception
        foreach ($fields->getFields() as $field) {
            $field->getBody()?->getContents();
        }
    }

    #[DataProvider('getParser')]
    public function testParseFileCountLimitAboveRequestFilesCount(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            for ($i = 0; $i < 4; $i++) {
                yield "Content-Disposition: form-data; name=\"file{$i}\"; filename=\"file{$i}.txt\"\r\n";
                yield "Content-Type: text/plain\r\n\r\n";
                yield "Hello, World!\r\n";
                yield "--" . self::BOUNDARY . ($i === 3 ? "--\r\n" : "\r\n");
            }
        })()));

        $form = $parser->parse($request, ParseOptions::create()->withFileCountLimit(10));

        static::assertCount(4, $form->getFiles());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFileCountLimitAboveRequestFilesCount(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            for ($i = 0; $i < 4; $i++) {
                yield "Content-Disposition: form-data; name=\"file{$i}\"; filename=\"file{$i}.txt\"\r\n";
                yield "Content-Type: text/plain\r\n\r\n";
                yield "Hello, World!\r\n";
                yield "--" . self::BOUNDARY . ($i === 3 ? "--\r\n" : "\r\n");
            }
        })()));

        $fields = $parser->parseStreamed($request, ParseOptions::create()->withFileCountLimit(10));
        $fieldCount = 0;
        foreach ($fields->getFields() as $field) {
            $fieldCount++;
            $field->getBody()?->getContents();
        }

        static::assertSame(4, $fieldCount);
    }

    #[DataProvider('getParser')]
    public function testParseFileCountLimitAtRequestFilesCount(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            for ($i = 0; $i < 4; $i++) {
                yield "Content-Disposition: form-data; name=\"file{$i}\"; filename=\"file{$i}.txt\"\r\n";
                yield "Content-Type: text/plain\r\n\r\n";
                yield "Hello, World!\r\n";
                yield "--" . self::BOUNDARY . ($i === 3 ? "--\r\n" : "\r\n");
            }
        })()));

        $form = $parser->parse($request, ParseOptions::create()->withFileCountLimit(4));

        static::assertCount(4, $form->getFiles());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFileCountLimitAtRequestFilesCount(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            for ($i = 0; $i < 4; $i++) {
                yield "Content-Disposition: form-data; name=\"file{$i}\"; filename=\"file{$i}.txt\"\r\n";
                yield "Content-Type: text/plain\r\n\r\n";
                yield "Hello, World!\r\n";
                yield "--" . self::BOUNDARY . ($i === 3 ? "--\r\n" : "\r\n");
            }
        })()));

        $fields = $parser->parseStreamed($request, ParseOptions::create()->withFileCountLimit(4));
        $fieldCount = 0;
        foreach ($fields->getFields() as $field) {
            $fieldCount++;
            $field->getBody()?->getContents();
        }

        static::assertSame(4, $fieldCount);
    }

    #[DataProvider('getParser')]
    public function testParseWithoutAllowingFilesWithoutExtension(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"file\"; filename=\"file\"\r\n";
            yield "Content-Type: text/plain\r\n\r\n";
            yield "Hello, World!\r\n";
            yield "--" . self::BOUNDARY . "--\r\n";
        })()));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('All uploaded files must have an extension.');

        $parser->parse($request, ParseOptions::create()->withAllowFilesWithoutExtensions(false));
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedWithoutAllowingFilesWithoutExtension(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"file\"; filename=\"file\"\r\n";
            yield "Content-Type: text/plain\r\n\r\n";
            yield "Hello, World!\r\n";
            yield "--" . self::BOUNDARY . "--\r\n";
        })()));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('All uploaded files must have an extension.');

        $form = $parser->parseStreamed($request, ParseOptions::create()->withAllowFilesWithoutExtensions(false));
        // iterate over the fields to trigger the exception
        foreach ($form->getFields() as $field) {
            $field->getBody()?->getContents();
        }
    }

    #[DataProvider('getParser')]
    public function testParseWithAllowingFilesWithoutExtension(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"file\"; filename=\"file\"\r\n";
            yield "Content-Type: text/plain\r\n\r\n";
            yield "Hello, World!\r\n";
            yield "--" . self::BOUNDARY . "--\r\n";
        })()));

        $form = $parser->parse($request, ParseOptions::create()->withAllowFilesWithoutExtensions(true));

        static::assertCount(1, $form->getFiles());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedWithAllowingFilesWithoutExtension(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"file\"; filename=\"file\"\r\n";
            yield "Content-Type: text/plain\r\n\r\n";
            yield "Hello, World!\r\n";
            yield "--" . self::BOUNDARY . "--\r\n";
        })()));

        $form = $parser->parseStreamed($request, ParseOptions::create()->withAllowFilesWithoutExtensions(true));
        $fieldCount = 0;
        foreach ($form->getFields() as $field) {
            $fieldCount++;
            $field->getBody()?->getContents();
        }

        static::assertSame(1, $fieldCount);
    }

    #[DataProvider('getParser')]
    public function testParseWithAllowedExtensionsOnly(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"file\"; filename=\"file.txt\"\r\n";
            yield "Content-Type: text/plain\r\n\r\n";
            yield "Hello, World!\r\n";
            yield "--" . self::BOUNDARY . "--\r\n";
        })()));

        $form = $parser->parse($request, ParseOptions::create()->withAllowedFileExtensions(['txt']));

        static::assertCount(1, $form->getFiles());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedWithAllowedExtensionsOnly(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"file\"; filename=\"file.txt\"\r\n";
            yield "Content-Type: text/plain\r\n\r\n";
            yield "Hello, World!\r\n";
            yield "--" . self::BOUNDARY . "--\r\n";
        })()));

        $form = $parser->parseStreamed($request, ParseOptions::create()->withAllowedFileExtensions(['txt']));
        $fieldCount = 0;
        foreach ($form->getFields() as $field) {
            $fieldCount++;
            $field->getBody()?->getContents();
        }

        static::assertSame(1, $fieldCount);
    }

    #[DataProvider('getParser')]
    public function testParseFailWithAllowedExtensionsOnly(ParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"file\"; filename=\"file.jpg\"\r\n";
            yield "Content-Type: image/jpeg\r\n\r\n";
            yield "Hello, World!\r\n";
            yield "--" . self::BOUNDARY . "--\r\n";
        })()));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The uploaded file has an invalid extension.');

        $parser->parse($request, ParseOptions::create()->withAllowedFileExtensions(['txt']));
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFailWithAllowedExtensionsOnly(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromIterable((static function (): iterable {
            yield "--" . self::BOUNDARY . "\r\n";
            yield "Content-Disposition: form-data; name=\"file\"; filename=\"file.jpg\"\r\n";
            yield "Content-Type: image/jpeg\r\n\r\n";
            yield "Hello, World!\r\n";
            yield "--" . self::BOUNDARY . "--\r\n";
        })()));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The uploaded file has an invalid extension.');

        $form = $parser->parseStreamed($request, ParseOptions::create()->withAllowedFileExtensions(['txt']));
        // iterate over the fields to trigger the exception
        foreach ($form->getFields() as $field) {
            $field->getBody()?->getContents();
        }
    }
}
