<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Dsn;

use Neu\Component\Broadcast\Exception\InvalidArgumentException;
use Psl\Str;
use Psl\Type;
use function Psl\Str;

final readonly class Tcp implements DsnInterface
{
    /**
     * @param non-empty-string $host
     * @param int<0, 65535> $port
     */
    public function __construct(
        public string $host,
        public int $port,
    )
    {
    }

    /**
     * @param non-empty-string $dsn
     *
     * @throws InvalidArgumentException
     */
    public static function fromString(#[\SensitiveParameter] string $dsn): self
    {
        if (!Str\Byte\starts_with($dsn, 'tcp://')) {
            throw new InvalidArgumentException('DSN must start with "tcp://"');
        }

        if (false === $params = parse_url($dsn)) {
            throw new InvalidArgumentException('Invalid TCP DSN');
        }

        try {
            Type\shape([
                'host' => Type\non_empty_string(),
                'port' => Type\u16(),
            ], true)->assert($params);
        } catch (Type\Exception\AssertException $exception) {
            throw new InvalidArgumentException('Invalid TCP DSN', previous: $exception);
        }

        return new self(
            $params['host'],
            $params['port'],
        );
    }

    public static function getScheme(): string
    {
        return 'tcp';
    }

    public function toString(): string
    {
        return
            self::getScheme().'://'.$this->host.
            ':'.((string) $this->port);
    }
}
