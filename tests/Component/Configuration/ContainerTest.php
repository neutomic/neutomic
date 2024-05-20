<?php

declare(strict_types=1);

namespace Neu\Tests\Component\Configuration;

use Closure;
use Neu\Component\Configuration\ConfigurationContainer;
use Neu\Component\Configuration\ConfigurationContainerInterface;
use Neu\Component\Configuration\Exception\InvalidEntryException;
use Neu\Component\Configuration\Exception\MissingEntryException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    public function testHas(): void
    {
        $configuration = new ConfigurationContainer([
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
        $configuration = new ConfigurationContainer([
            'foo' => $this,
        ]);

        static::assertSame($this, $configuration->get('foo'));
    }

    public function testGetThrowsForUndefinedEntries(): void
    {
        $configuration = new ConfigurationContainer([]);

        $this->expectException(MissingEntryException::class);
        $this->expectExceptionMessage('Entry "foo" does not exist within the container.');

        $configuration->get('foo');
    }

    public function testTypedGetters(): void
    {
        $configuration = new ConfigurationContainer([
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

        static::assertSame(12, $configuration->getInt('foo'));
        static::assertSame(12.0, $configuration->getFloat('foo'));
        static::assertSame('12', $configuration->getString('foo'));
        static::assertFalse($configuration->getBool('bar'));
        static::assertTrue($configuration->getBool('baz'));
        static::assertTrue($configuration->getBool('qux'));

        static::assertSame([1, 'two'], $configuration->getContainer('quxx')->getContainer('foo')->getIndices());
    }

    #[DataProvider('provideInvalidGetOperations')]
    public function testInvalidGetOperations(array $entries, Closure $operation, string $message): void
    {
        $container = new ConfigurationContainer($entries);

        $this->expectException(InvalidEntryException::class);
        $this->expectExceptionMessage($message);

        $operation($container);
    }

    /**
     * @return iterable<array{array, Closure(ConfigurationContainerInterface): mixed, string}>
     */
    public static function provideInvalidGetOperations(): iterable
    {
        yield [
            ['foo' => [1, 2, 3]],
            static fn(ConfigurationContainerInterface $container) => $container->getString('foo'),
            'Entry "foo" value cannot be coerced into the expected type string'
        ];

        yield [
            ['foo' => [1, 2, 3]],
            static fn(ConfigurationContainerInterface $container) => $container->getInt('foo'),
            'Entry "foo" value cannot be coerced into the expected type int'
        ];

        yield [
            ['foo' => [1, 2, 3]],
            static fn(ConfigurationContainerInterface $container) => $container->getFloat('foo'),
            'Entry "foo" value cannot be coerced into the expected type float'
        ];

        yield [
            ['foo' => 'hello'],
            static fn(ConfigurationContainerInterface $container) => $container->getBool('foo'),
            'Entry "foo" value cannot be coerced into the expected type bool'
        ];

        yield [
            ['foo' => 'hello'],
            static fn(ConfigurationContainerInterface $container) => $container->getContainer('foo'),
            'Entry "foo" value cannot be coerced into the expected type dict<array-key, mixed>'
        ];
    }

    public function testMerge(): void
    {
        $configuration1 = new ConfigurationContainer(['foo' => '12']);
        $configuration2 = new ConfigurationContainer(['bar' => '13']);
        $configuration3 = $configuration1->merge($configuration2);

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
        $configuration1 = new ConfigurationContainer(['foo' => ['12'], 'bar' => ['qux' => ['1']]]);
        $configuration2 = new ConfigurationContainer(['bar' => ['baz' => '2', 'qux' => ['2']]]);
        $configuration3 = $configuration1->merge($configuration2);

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
