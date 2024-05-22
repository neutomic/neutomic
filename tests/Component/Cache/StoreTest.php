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

namespace Neu\Tests\Component\Cache;

use Neu\Component\Cache;
use Neu\Component\Cache\Exception\UnavailableItemException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl;

final class StoreTest extends TestCase
{
    /**
     * @return iterable<array{0: Cache\Store, 1: Cache\Driver\DriverInterface}>
     */
    public static function provideStore(): iterable
    {
        $driver = new Cache\Driver\LocalDriver(100, 5);
        yield [new Cache\Store($driver), $driver];
    }

    #[DataProvider('provideStore')]
    public function testAtomic(Cache\Store $cache, Cache\Driver\DriverInterface $driver): void
    {
        $ref = new Psl\Ref(false);
        $computer = static function () use ($ref): string {
            Psl\Async\sleep(0.02);
            $ref->value = true;

            return 'azjezz';
        };

        $user = $cache->compute('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertTrue($ref->value);

        $ref->value = false;
        $user = $cache->compute('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertFalse($ref->value);

        $driver->delete('user');

        $one = Psl\Async\run(static fn () => $cache->compute('user', $computer, ttl: 1));
        Psl\Async\later();
        $two = Psl\Async\run(static fn () => $cache->compute('user', $computer, ttl: 1));
        $user = $one->await();
        static::assertSame('azjezz', $user);
        static::assertTrue($ref->value);

        $ref->value = false;
        $user = $two->await();
        static::assertSame('azjezz', $user);
        static::assertFalse($ref->value);

        $driver->delete('user');

        $user = $cache->compute('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertTrue($ref->value);
        $ref->value = false;
        $cache->delete('user');

        $ref = new Psl\Ref('a');
        Psl\Async\Scheduler::defer(static function () use ($cache, $computer, $ref): void {
            // compute the item again.
            $user = $cache->compute('user', $computer, ttl: 1);
            self::assertSame('azjezz', $user);
            self::assertSame('b', $ref->value);
            $ref->value = 'c';
        });

        Psl\Async\later();

        static::assertSame('a', $ref->value);
        $ref->value = 'b';
        $cache->delete('user'); // will wait until defer is finished.
        static::assertSame('c', $ref->value);
    }

    #[DataProvider('provideStore')]
    public function testUpdate(Cache\Store $cache, Cache\Driver\DriverInterface $_driver): void
    {
        $ref = new Psl\Ref(false);
        $computer = static function () use ($ref): string {
            $ref->value = true;
            Psl\Async\sleep(0.02);

            return 'azjezz';
        };

        $user = $cache->compute('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertTrue($ref->value);

        $ref->value = false;
        $user = $cache->update('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertTrue($ref->value);

        $ref->value = false;
        $user = $cache->compute('user', $computer, ttl: 1);
        static::assertSame('azjezz', $user);
        static::assertFalse($ref->value);
    }

    #[DataProvider('provideStore')]
    public function testDelete(Cache\Store $cache, Cache\Driver\DriverInterface $driver): void
    {
        $cache->compute('user', function (): string {
            $this->addToAssertionCount(1);

            return 'azjezz';
        });

        $user = $cache->compute('user', static function (): string {
            self::fail('value should been in cache.');
        });

        static::assertSame('azjezz', $driver->get('user'));
        static::assertSame('azjezz', $user);

        $cache->delete('user');

        $this->expectException(UnavailableItemException::class);
        $this->expectExceptionMessage('No cache item is associated with the "');

        $driver->get('user');
    }
}
