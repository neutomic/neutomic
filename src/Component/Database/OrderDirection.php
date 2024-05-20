<?php

declare(strict_types=1);

namespace Neu\Component\Database;

enum OrderDirection: string
{
    case Ascending = 'ASC';
    case Descending = 'DESC';
}
