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

namespace Neu\Component\Cache\Driver\IPCDriver;

final readonly class SetMessage extends AbstractMessage
{
    /**
     * @param non-empty-string $key
     * @param int<1, max>|null $ttl
     */
    public function __construct(
        public string $key,
        public mixed $value,
        public null|int $ttl = null
    ) {
        parent::__construct();
    }
}
