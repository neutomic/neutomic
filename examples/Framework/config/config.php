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

use Neu\Component\Http\Session\Configuration\CacheLimiter;

return [
    'framework' => [
        'middleware' => [
            'compression' => false,
            'static-content' => false,
        ]
    ],
    'http' => [
        'server' => [
            'sockets' => [[
                'host' => '127.0.0.1',
                'port' => 1337,
            ]]
        ],
        'session' => [
            'cache' => [
                'limiter' => CacheLimiter::Public,
            ]
        ]
    ]
];
