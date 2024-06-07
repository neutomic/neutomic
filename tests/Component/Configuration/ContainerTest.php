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

namespace Neu\Tests\Component\Configuration;

use Closure;
use Neu\Component\DependencyInjection\Configuration\Document;
use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Exception\InvalidEntryException;
use Neu\Component\DependencyInjection\Exception\MissingEntryException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Type;

final class ContainerTest extends TestCase
{
    public function testHas(): void
    {
        $configuration = new Document([
            'foo' => null,
            'bar' => false,
            'baz' => []
        ]);

        static::assertTrue($configuration->has('foo'));
        static::assertTrue($configuration->has('bar'));
        static::assertTrue($configuration->has('baz'));
        static::assertFalse($configuration->has('qux'));
    }

    public function testGet(): void
    {
        $configuration = new Document([
            'foo' => $this,
        ]);

        static::assertSame($this, $configuration->get('foo'));
    }

    public function testGetThrowsForUndefinedEntries(): void
    {
        $configuration = new Document([]);

        $this->expectException(MissingEntryException::class);
        $this->expectExceptionMessage('Entry "foo" does not exist within the container.');

        $configuration->get('foo');
    }

    public function testTypedGetters(): void
    {
        $configuration = new Document([
            'foo' => '12',
            'bar' => '0',
            'baz' => '1',
            'qux' => '1',
            'quxx' => [
                'foo' => [1 => 'foo', 'two' => 'bar'],
                'bar' => [1, 2, 3],
                'baz' => ['a', 'b', 'c'],
            ],
        ]);

        static::assertSame('12', $configuration->get('foo'));
        static::assertSame(12, $configuration->getOfType('foo', Type\int()));

        static::assertSame([1, 'two'], $configuration->getDocument('quxx')->getDocument('foo')->getIndices());
    }

    #[DataProvider('provideInvalidGetOperations')]
    public function testInvalidGetOperations(array $entries, Closure $operation, string $message): void
    {
        $container = new Document($entries);

        $this->expectException(InvalidEntryException::class);
        $this->expectExceptionMessage($message);

        $operation($container);
    }

    /**
     * @return iterable<array{array, Closure(DocumentInterface): mixed, string}>
     */
    public static function provideInvalidGetOperations(): iterable
    {
        yield [
            ['foo' => 'hello'],
            static fn (DocumentInterface $container) => $container->getDocument('foo'),
            'Entry "foo" value cannot be coerced into the expected type "dict<array-key, mixed>"'
        ];
    }

    public function testMerge(): void
    {
        $configuration1 = new Document(['foo' => '12']);
        $configuration2 = new Document(['bar' => '13']);
        $configuration3 = $configuration1->replace($configuration2);

        static::assertNotSame($configuration3, $configuration1);
        static::assertNotSame($configuration3, $configuration2);

        static::assertTrue($configuration1->has('foo'));
        static::assertFalse($configuration1->has('bar'));
        static::assertFalse($configuration2->has('foo'));
        static::assertTrue($configuration2->has('bar'));
        static::assertTrue($configuration3->has('foo'));
        static::assertTrue($configuration3->has('bar'));

        static::assertSame(['foo' => '12'], $configuration1->getAll());
        static::assertSame(['bar' => '13'], $configuration2->getAll());
        static::assertSame(['foo' => '12', 'bar' => '13'], $configuration3->getAll());
    }

    public function testReplacesRecursive(): void
    {
        $configuration1 = new Document(['foo' => ['12'], 'bar' => ['qux' => ['1']]]);
        $configuration2 = new Document(['bar' => ['baz' => '2', 'qux' => ['2']]]);
        $configuration3 = $configuration1->replace($configuration2);

        static::assertNotSame($configuration3, $configuration1);
        static::assertNotSame($configuration3, $configuration2);

        static::assertTrue($configuration1->has('foo'));
        static::assertTrue($configuration1->has('bar'));
        static::assertFalse($configuration2->has('foo'));
        static::assertTrue($configuration2->has('bar'));
        static::assertTrue($configuration3->has('foo'));
        static::assertTrue($configuration3->has('bar'));

        static::assertSame(['foo' => ['12'], 'bar' => ['qux' => ['1']]], $configuration1->getAll());
        static::assertSame(['bar' => ['baz' => '2', 'qux' => ['2']]], $configuration2->getAll());
        static::assertSame(['foo' => ['12'], 'bar' => ['qux' => ['2'], 'baz' => '2']], $configuration3->getAll());
    }
}
