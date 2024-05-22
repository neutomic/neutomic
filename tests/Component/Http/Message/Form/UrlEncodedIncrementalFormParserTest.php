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

use Neu\Component\Http\Message\Form\UrlEncodedIncrementalFormParser;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\Request;
use Neu\Component\Http\Message\RequestBody;
use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Str;

final class UrlEncodedIncrementalFormParserTest extends TestCase
{
    public function testParseNoContentType(): void
    {
        $request = Request::create(Method::Get, 'https://example.com');

        $parser = new UrlEncodedIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([], $fields);
    }

    public function testParseNoBody(): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $parser = new UrlEncodedIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([], $fields);
    }

    public function testParseEmptyFormData(): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString(''));

        $parser = new UrlEncodedIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([], $fields);
    }

    public function testParseFieldWithoutValue(): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=&b=bravo'));

        $parser = new UrlEncodedIncrementalFormParser();
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
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('na%20me=value%20with%20spaces&name2=val%40ue2'));

        $parser = new UrlEncodedIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([
            'na me' => 'value with spaces',
            'name2' => 'val@ue2'
        ], $fields);
    }


    public function testParseMultipleConsecutiveAmpersands(): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=alpha&&b=bravo&&&c=charlie'));

        $parser = new UrlEncodedIncrementalFormParser();
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

    public function testParseFieldWithoutEqualsSign(): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a&b=bravo'));

        $parser = new UrlEncodedIncrementalFormParser();
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


    public function testParseSingleFieldWithValue(): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=alpha'));

        $parser = new UrlEncodedIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([
            'a' => 'alpha'
        ], $fields);
    }

    public function testParseMultipleFieldsWithSimpleValues(): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=alpha&b=bravo&c=charlie'));

        $parser = new UrlEncodedIncrementalFormParser();
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

    public function testParseMessyChunks(): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromIterable([
            'a=alpha&b=br',
            'avo&c=charlie&',
            'd=delta',
            '&th',
            'at\'s=',
            'it&x=1'
        ]));

        $parser = new UrlEncodedIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([
            'a' => 'alpha',
            'b' => 'bravo',
            'c' => 'charlie',
            'd' => 'delta',
            'that\'s' => 'it',
            'x' => '1'
        ], $fields);
    }

    public function testParseValueSpreadOverMultipleChunks(): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromIterable([
            'a=x',
            'x',
            'x',
            'x',
            'x',
            'x',
            'x',
            'x&b=y',
        ]));

        $parser = new UrlEncodedIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()->getContents();
        }

        static::assertSame([
            'a' => 'xxxxxxxx',
            'b' => 'y'
        ], $fields);
    }

    public function testParserIsAsync(): void
    {
        $reference = new Psl\Ref('');

        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromIterable((static function () use ($reference): iterable {
            yield 'key=';
            yield 'x';
            for ($i = 0; $i < 999; $i++) {
                $reference->value .= 'w';

                yield 'x';

                Psl\Async\sleep(0.0001);
            }
        })()));

        $parser = new UrlEncodedIncrementalFormParser();
        $form = $parser->parse($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = '';

            while ($chunk = $field->getBody()->getChunk()) {
                $reference->value .= 'r';

                $fields[$field->getName()] .= $chunk;
            }
        }

        static::assertSame(['key' => Str\repeat('x', 1000)], $fields);

        static::assertSame(Str\repeat('wr', 999) . 'r', $reference->value);
    }
}
