<?php

declare(strict_types=1);

namespace Neu\Tests\Component\Broadcast;

use Neu\Component\Broadcast\Dsn;
use Neu\Component\Broadcast\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DsnTest extends TestCase
{
    public function testUnix(): void
    {
        $dsn = Dsn\from_string('unix:///var/run/neutomic-broadcast.sock');

        self::assertInstanceOf(\Neu\Component\Broadcast\Dsn\Unix::class, $dsn);
        self::assertSame('/var/run/neutomic-broadcast.sock', $dsn->path);
        self::assertSame('unix:///var/run/neutomic-broadcast.sock', $dsn->toString());
    }

    public function testPlainTcp(): void
    {
        $dsn = Dsn\from_string('tcp://127.0.0.1:1234');

        self::assertInstanceOf(\Neu\Component\Broadcast\Dsn\Tcp::class, $dsn);
        self::assertSame('127.0.0.1', $dsn->host);
        self::assertSame('tcp://127.0.0.1:1234', $dsn->toString());
    }

    #[DataProvider('provideInvalidDsn')]
    public function testUnsupportedScheme(string $dsn): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported DSN scheme');

        Dsn\from_string($dsn);
    }

    #[DataProvider('provideInvalidDsn')]
    public function testInvalidUnixDsn(string $dsn): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DSN must start with "unix://"');

        \Neu\Component\Broadcast\Dsn\Unix::fromString('/var/run/neutomic-broadcast.sock');
    }

    #[DataProvider('provideInvalidDsn')]
    public function testInvalidTcpDsn(string $dsn): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DSN must start with "tcp://"');

        \Neu\Component\Broadcast\Dsn\Tcp::fromString('/var/run/neutomic-broadcast.sock');
    }

    public static function provideInvalidDsn(): iterable
    {
        yield ['foo://127.0.0.1:1234'];
        yield ['foo:///var/run/neutomic-broadcast.sock'];
        yield ['127.0.0.1:1234'];
        yield ['/var/run/neutomic-broadcast.sock'];
        yield ['foo://'];
    }
}
