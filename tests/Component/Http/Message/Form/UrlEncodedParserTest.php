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
use Neu\Component\Http\Message\Form\ParseOptions;
use Neu\Component\Http\Message\Form\Parser;
use Neu\Component\Http\Message\Form\ParserInterface;
use Neu\Component\Http\Message\Form\StreamedParserInterface;
use Neu\Component\Http\Message\Form\UrlEncodedParser;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\Request;
use Neu\Component\Http\Message\RequestBody;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Str;

/**
 * @psalm-suppress MissingThrowsDocblock
 */
class UrlEncodedParserTest extends TestCase
{
    /**
     * @return Generator<int, list<ParserInterface>, mixed, void>
     */
    public static function getParser(): Generator
    {
        yield [new UrlEncodedParser()];
        yield [new Parser()];
    }

    /**
     * @return Generator<int, list<StreamedParserInterface>, mixed, void>
     */
    public static function getStreamingParser(): Generator
    {
        yield [new UrlEncodedParser()];
        yield [new Parser()];
    }

    #[DataProvider('getParser')]
    public function testParseNoContentType(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');

        $form = $parser->parse($request);

        static::assertSame([], $form->getFields());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedNoContentType(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');

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
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $form = $parser->parse($request);

        static::assertSame([], $form->getFields());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedNoBody(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');

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
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString(''));

        $form = $parser->parse($request);

        static::assertSame([], $form->getFields());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedEmptyFormData(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString(''));

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
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=&b=bravo'));

        $form = $parser->parse($request);

        static::assertCount(2, $form->getFields());
        static::assertSame('', $form->getFirstFieldByName('a')?->getBody()?->getContents());
        static::assertSame('bravo', $form->getFirstFieldByName('b')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFieldWithoutValue(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=&b=bravo'));

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
    public function testParseMultipleFieldsWithSameName(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=alpha&a=bravo&a=charlie'));

        $form = $parser->parse($request);

        static::assertCount(3, $form->getFields());
        static::assertSame('alpha', $form->getFirstFieldByName('a')?->getBody()?->getContents());
        static::assertSame('bravo', $form->getFieldsByName('a')[1]->getBody()?->getContents());
        static::assertSame('charlie', $form->getFieldsByName('a')[2]->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedMultipleFieldsWithSameName(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=alpha&a=bravo&a=charlie'));

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()][] = $field->getBody()?->getContents();
        }

        static::assertSame([
            'a' => ['alpha', 'bravo', 'charlie']
        ], $fields);
    }

    #[DataProvider('getParser')]
    public function testParseURLEncodedCharacters(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('na%20me=value%20with%20spaces&name2=val%40ue2'));

        $form = $parser->parse($request);

        static::assertCount(2, $form->getFields());
        static::assertSame('value with spaces', $form->getFirstFieldByName('na me')?->getBody()?->getContents());
        static::assertSame('val@ue2', $form->getFirstFieldByName('name2')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedURLEncodedCharacters(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('na%20me=value%20with%20spaces&name2=val%40ue2'));

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()?->getContents();
        }

        static::assertSame([
            'na me' => 'value with spaces',
            'name2' => 'val@ue2'
        ], $fields);
    }

    #[DataProvider('getParser')]
    public function testParseMultipleConsecutiveAmpersands(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=alpha&&b=bravo&&&c=charlie'));

        $form = $parser->parse($request);

        static::assertCount(3, $form->getFields());
        static::assertSame('alpha', $form->getFirstFieldByName('a')?->getBody()?->getContents());
        static::assertSame('bravo', $form->getFirstFieldByName('b')?->getBody()?->getContents());
        static::assertSame('charlie', $form->getFirstFieldByName('c')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedMultipleConsecutiveAmpersands(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=alpha&&b=bravo&&&c=charlie'));

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
    public function testParseFieldWithoutEqualsSign(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a&b=bravo'));

        $form = $parser->parse($request);

        static::assertCount(2, $form->getFields());
        static::assertSame('', $form->getFirstFieldByName('a')?->getBody()?->getContents());
        static::assertSame('bravo', $form->getFirstFieldByName('b')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFieldWithoutEqualsSign(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a&b=bravo'));

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
    public function testParseSingleFieldWithValue(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=alpha'));

        $form = $parser->parse($request);

        static::assertCount(1, $form->getFields());
        static::assertSame('alpha', $form->getFirstFieldByName('a')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedSingleFieldWithValue(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=alpha'));

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
    public function testParseMultipleFieldsWithSimpleValues(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=alpha&b=bravo&c=charlie'));

        $form = $parser->parse($request);

        static::assertCount(3, $form->getFields());
        static::assertSame('alpha', $form->getFirstFieldByName('a')?->getBody()?->getContents());
        static::assertSame('bravo', $form->getFirstFieldByName('b')?->getBody()?->getContents());
        static::assertSame('charlie', $form->getFirstFieldByName('c')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedMultipleFieldsWithSimpleValues(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=alpha&b=bravo&c=charlie'));

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
    public function testParseMessyChunks(ParserInterface $parser): void
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

        $form = $parser->parse($request);

        static::assertCount(6, $form->getFields());
        static::assertSame('alpha', $form->getFirstFieldByName('a')?->getBody()?->getContents());
        static::assertSame('bravo', $form->getFirstFieldByName('b')?->getBody()?->getContents());
        static::assertSame('charlie', $form->getFirstFieldByName('c')?->getBody()?->getContents());
        static::assertSame('delta', $form->getFirstFieldByName('d')?->getBody()?->getContents());
        static::assertSame('it', $form->getFirstFieldByName('that\'s')?->getBody()?->getContents());
        static::assertSame('1', $form->getFirstFieldByName('x')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedMessyChunks(StreamedParserInterface $parser): void
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

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()?->getContents();
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

    #[DataProvider('getParser')]
    public function testParseValueSpreadOverMultipleChunks(ParserInterface $parser): void
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

        $form = $parser->parse($request);

        static::assertCount(2, $form->getFields());
        static::assertSame('xxxxxxxx', $form->getFirstFieldByName('a')?->getBody()?->getContents());
        static::assertSame('y', $form->getFirstFieldByName('b')?->getBody()?->getContents());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedValueSpreadOverMultipleChunks(StreamedParserInterface $parser): void
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

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field->getBody()?->getContents();
        }

        static::assertSame([
            'a' => 'xxxxxxxx',
            'b' => 'y'
        ], $fields);
    }

    #[DataProvider('getStreamingParser')]
    public function testStreamedParsingIsAsync(StreamedParserInterface $parser): void
    {
        /** @var Psl\Ref<string> $reference */
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

        $form = $parser->parseStreamed($request);
        $fields = [];
        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = '';

            while ($chunk = $field->getBody()?->getChunk()) {
                $reference->value .= 'r';

                $fields[$field->getName()] .= $chunk;
            }
        }

        static::assertSame(['key' => Str\repeat('x', 1000)], $fields);

        static::assertSame(Str\repeat('wr', 999) . 'r', $reference->value);
    }

    #[DataProvider('getParser')]
    public function testParseFieldCountLimitOption(ParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=1&b=2&c=3&d=4&e=5'));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The number of fields in the form data exceeds the limit of 3.');

        $parser->parse($request, ParseOptions::fromFieldCountLimit(3));
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFieldCountLimitOption(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=1&b=2&c=3&d=4&e=5'));

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
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=1&b=2&c=3&d=4&e=5'));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('The number of fields in the form data exceeds the limit of 0.');

        $parser->parse($request, ParseOptions::fromFieldCountLimit(0));
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFieldCountLimitOptionOfZero(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=1&b=2&c=3&d=4&e=5'));

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
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=1&b=2&c=3&d=4&e=5'));

        $form = $parser->parse($request, ParseOptions::fromFieldCountLimit(10));

        static::assertCount(5, $form->getFields());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFieldCountLimitAboveRequestFieldsCount(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=1&b=2&c=3&d=4&e=5'));

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
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=1&b=2&c=3&d=4&e=5'));

        $form = $parser->parse($request, ParseOptions::fromFieldCountLimit(5));

        static::assertCount(5, $form->getFields());
    }

    #[DataProvider('getStreamingParser')]
    public function testParseStreamedFieldCountLimitAtRequestFieldsCount(StreamedParserInterface $parser): void
    {
        $request = Request::create(Method::Get, 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request = $request->withBody(RequestBody::fromString('a=1&b=2&c=3&d=4&e=5'));

        $fields = $parser->parseStreamed($request, ParseOptions::fromFieldCountLimit(5));
        $fieldCount = 0;
        foreach ($fields->getFields() as $field) {
            $fieldCount++;
            $field->getBody()?->getContents();
        }

        static::assertSame(5, $fieldCount);
    }
}
