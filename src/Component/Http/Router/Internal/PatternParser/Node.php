<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\Internal\PatternParser;

/**
 * @internal
 */
interface Node
{
    public function toStringForDebug(): string;
    public function asRegexp(string $delimiter): string;
}
