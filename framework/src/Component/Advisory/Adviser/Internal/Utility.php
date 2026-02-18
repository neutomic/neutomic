<?php

declare(strict_types=1);

/*
 * This file is part of the Neutomic package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neu\Component\Advisory\Adviser\Internal;

use function preg_match;

enum Utility
{
    public static function parseValue(string $value): int
    {
        if (preg_match('/^(\d+)(.)$/', $value, $matches)) {
            if ($matches[2] === 'G') {
                $value = (int) $matches[1] * 1024 * 1024 * 1024;
            } elseif ($matches[2] === 'M') {
                $value = (int) $matches[1] * 1024 * 1024;
            } elseif ($matches[2] === 'K') {
                $value = (int) $matches[1] * 1024;
            } else {
                $value = (int) $matches[1];
            }
        } else {
            $value = (int) $value;
        }

        return $value;
    }
}
