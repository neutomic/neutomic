<?php

declare(strict_types=1);

namespace Neu\Component\Http\Session\Configuration;

enum CacheLimiter: string
{
    case NoCache = 'nocache';
    case Public = 'public';
    case Private = 'private';
    case PrivateNoExpire = 'private-no-expire';
}
