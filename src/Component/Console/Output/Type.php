<?php

declare(strict_types=1);

namespace Neu\Component\Console\Output;

enum Type: int
{
    case Normal = 1;
    case Raw = 2;
    case Plain = 4;
}
