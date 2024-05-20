<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server\Cluster\Internal;

use Neu\Component\Http\Exception\RuntimeException;

(static function () use ($argc, $argv): void {
    if ($argc !== 2) {
        throw new RuntimeException('Invalid number of arguments provided to the worker.');
    }

    [$_, $entrypoint] = $argv;

    @require $entrypoint;
})();
