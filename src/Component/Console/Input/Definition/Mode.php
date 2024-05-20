<?php

declare(strict_types=1);

namespace Neu\Component\Console\Input\Definition;

enum Mode: int
{
    case Optional = 0;
    case Required = 1;
}
