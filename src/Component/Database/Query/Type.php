<?php

declare(strict_types=1);

namespace Neu\Component\Database\Query;

enum Type
{
    case Insert;
    case Select;
    case Update;
    case Delete;
}
