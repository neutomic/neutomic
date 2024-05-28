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
use Neu\Component\Http\Session\Exception\RuntimeException;

interface InitializerInterface
{
    /**
     * Initialize the give request with a session instance.
     *
     * The returned request must return true for {@see RequestInterface::hasSession()} call.
     *
     * @throws RuntimeException If an error occurs while initializing the request.
     */
    public function initialize(RequestInterface $request): RequestInterface;
}
