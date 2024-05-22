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

namespace Neu\Tests\Component\Cache\Driver;

use Neu\Component\Cache;
use PHPUnit\Framework\TestCase;

final class LocalDriverTest extends TestCase
{
    public function testSetGetDelete(): void
    {
        $driver = new Cache\Driver\LocalDriver();

        $driver->set('user', 'azjezz');
        static::assertSame('azjezz', $driver->get('user'));
        $driver->set('user', 'trowski');
        static::assertSame('trowski', $driver->get('user'));
        $driver->delete('user');

        try {
            $driver->get('user');
            static::fail('Expected exception to be thrown.');
        } catch (Cache\Exception\UnavailableItemException) {
            $this->addToAssertionCount(1);
        }

        $driver->set('user', 'azjezz', 0);
        $this->expectException(Cache\Exception\UnavailableItemException::class);
        $driver->get('user');
    }

    public function testSizeLimit(): void
    {
        $driver = new Cache\Driver\LocalDriver(size: 2);

        $driver->set('foo', 'value');
        static::assertSame('value', $driver->get('foo'));
        $driver->set('bar', 'value');
        static::assertSame('value', $driver->get('bar'));
        $driver->set('baz', 'value');
        static::assertSame('value', $driver->get('baz'));

        $this->expectException(Cache\Exception\UnavailableItemException::class);

        $driver->get('foo');
    }
}
