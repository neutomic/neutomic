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

namespace Neu\Component\Http\Session\Initializer;

use Neu\Component\Http\Message\RequestInterface;

interface InitializerInterface
{
    /**
     * Initialize the give request with a session instance.
     *
     * The returned request must return true for {@see RequestInterface::hasSession()} call.
     */
    public function initialize(RequestInterface $request): RequestInterface;
}
