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

abstract readonly class AbstractMessage implements MessageInterface
{
    /**
     * @var non-empty-string
     */
    private string $messageId;

    public function __construct()
    {
        $this->messageId = uniqid('', true);
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }
}
