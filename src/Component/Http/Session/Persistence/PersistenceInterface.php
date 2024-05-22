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

namespace Neu\Component\Http\Session\Persistence;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;

interface PersistenceInterface
{
    /**
     * Persist the session data from the give request.
     *
     * Persists the session data, returning a response instance with any
     * artifacts required to return to the client.
     */
    public function persist(RequestInterface $request, ResponseInterface $response): ResponseInterface;
}
