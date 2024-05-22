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

namespace Neu\Tests\Component\Http\Message\Form;

use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Form\FileInterface;
use Neu\Component\Http\Message\Form\MultipartIncrementalFormParser;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\Request;
use Neu\Component\Http\Message\RequestBody;
use PHPUnit\Framework\TestCase;

final class MultipartIncrementalFormParserTest extends TestCase
{
    private const string BOUNDARY = '---------------------------97462980627483989762330262428';

    public function testParseNoContentType(): void
    {
        $request = Request::create(Method::Post, 'https://example.com');

        $parser = new MultipartIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([], $fields);
    }

    public function testParseNoBody(): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);

        $parser = new MultipartIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([], $fields);
    }

    public function testParseEmptyFormData(): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString("--" . self::BOUNDARY . "--\r\n"));

        $parser = new MultipartIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([], $fields);
    }

    public function testParseFieldWithoutValue(): void
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

        $parser = new MultipartIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([
            'a' => '',
            'b' => 'bravo'
        ], $fields);
    }

    public function testParseURLEncodedCharacters(): void
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

        $parser = new MultipartIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([
            'na%20me' => 'value%20with%20spaces',
            'name2' => 'val%40ue2'
        ], $fields);
    }

    public function testParseMultipleConsecutiveBoundaries(): void
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

        $parser = new MultipartIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([
            'a' => 'alpha',
            'b' => 'bravo',
            'c' => 'charlie'
        ], $fields);
    }

    public function testParseFieldWithoutContentDisposition(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Missing content-disposition header within multipart form');

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

        $parser = new MultipartIncrementalFormParser();

        foreach ($parser->parse($request)->getFields() as $field) {
            $field->getBody()->getContents();
        }
    }

    public function testParseSingleFieldWithValue(): void
    {
        $request = Request::create(Method::Post, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . self::BOUNDARY);
        $request = $request->withBody(RequestBody::fromString(
            "--" . self::BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"a\"\r\n\r\n" .
            "alpha\r\n" .
            "--" . self::BOUNDARY . "--\r\n"
        ));

        $parser = new MultipartIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([
            'a' => 'alpha'
        ], $fields);
    }

    public function testParseFileField(): void
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

        $parser = new MultipartIncrementalFormParser();
        $form = $parser->parse($request);

        foreach ($form->getFields() as $field) {
            static::assertInstanceOf(FileInterface::class, $field);

            static::assertSame('file.txt', $field->getFilename());
            static::assertSame('text/plain', $field->getMimeType());
            static::assertSame('txt', $field->getExtension());
            static::assertSame('Hello, World!', $field->getBody()->getContents());
        }
    }

    public function testHeadersAreParsedCorrectly(): void
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

        $parser = new MultipartIncrementalFormParser();
        $form = $parser->parse($request);

        foreach ($form->getFields() as $field) {
            static::assertInstanceOf(FileInterface::class, $field);

            static::assertSame('file.txt', $field->getFilename());
            static::assertSame('text/plain', $field->getMimeType());
            static::assertSame('Hello, World!', $field->getBody()->getContents());
            static::assertSame(['value'], $field->getHeader('X-Header'));
            static::assertSame(['text/plain'], $field->getHeader('Content-Type'));
            static::assertSame(['form-data; name="file"; filename="file.txt"'], $field->getHeader('Content-Disposition'));
        }
    }
}
