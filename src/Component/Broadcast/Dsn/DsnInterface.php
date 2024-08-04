<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Dsn;

interface DsnInterface
{
    /**
     * @return non-empty-string
     */
    public static function getScheme(): string;

    /**
     * @return non-empty-string
     */
    public function toString(): string;
}
