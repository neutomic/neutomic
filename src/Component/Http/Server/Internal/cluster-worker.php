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

namespace Neu\Component\Http\Server\Cluster\Internal;

use Neu\Component\Http\Exception\RuntimeException;

(static function () use ($argc, $argv): void {
    if ($argc !== 2) {
        throw new RuntimeException('Invalid number of arguments provided to the worker.');
    }

    /** @psalm-suppress PossiblyNullArrayAccess */
    [$_, $entrypoint] = $argv;

    /** @psalm-suppress UnresolvableInclude */
    require $entrypoint;
})();
