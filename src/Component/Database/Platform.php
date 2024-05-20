<?php

declare(strict_types=1);

namespace Neu\Component\Database;

enum Platform: string
{
    case Mysql = 'mysql';
    case Postgres = 'postgres';
}
