<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server;

enum Status: string
{
    case Starting = 'Starting';
    case Started = 'Started';
    case Stopping = 'Stopping';
    case Stopped = 'Stopped';
}
