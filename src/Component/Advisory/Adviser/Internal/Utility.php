<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\Adviser\Internal;

use function preg_match;

enum Utility
{
    public static function parseValue(string $value): int
    {
        if (preg_match('/^(\d+)(.)$/', $value, $matches)) {
            if ($matches[2] === 'G') {
                $value = $matches[1] * 1024 * 1024 * 1024;
            } elseif ($matches[2] === 'M') {
                $value = $matches[1] * 1024 * 1024;
            } elseif ($matches[2] === 'K') {
                $value = $matches[1] * 1024;
            }
        } else {
            $value = (int) $value;
        }

        return $value;
    }
}
