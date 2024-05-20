<?php

declare(strict_types=1);

namespace Neu\Component\Console\Output;

enum Verbosity: int
{
    case Quite = 16;
    case Normal = 32;
    case Verbose = 64;
    case VeryVerbose = 128;
    case Debug = 256;

    public function isVerbose(): bool
    {
        return $this->value >= self::Verbose->value;
    }
}
