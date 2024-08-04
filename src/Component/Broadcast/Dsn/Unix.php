<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Dsn;

use Neu\Component\Broadcast\Exception\InvalidArgumentException;
use Psl\Str;

final readonly class Unix implements DsnInterface
{
    /**
     * @param non-empty-string $path
     */
    public function __construct(
        public string $path,
    )
    {
    }

    /**
     * @param non-empty-string $dsn
     * @throws InvalidArgumentException
     */
    public static function fromString(string $dsn): self
    {
        if (!Str\Byte\starts_with($dsn, 'unix://')) {
            throw new InvalidArgumentException('DSN must start with "unix://"');
        }

        $path = Str\Byte\slice($dsn, Str\Byte\length('unix://'));

        if ('' === $path) {
            throw new InvalidArgumentException('DSN path must not be empty');
        }

        return new self($path);
    }

    public static function getScheme(): string
    {
        return 'unix';
    }

    public function toString(): string
    {
        return self::getScheme().'://'.$this->path;
    }
}
