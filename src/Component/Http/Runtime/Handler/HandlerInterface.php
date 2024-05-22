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

namespace Neu\Component\Http\Runtime\Handler;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;

/**
 * Handles a server request and produces a response.
 *
 * An HTTP request handler process an HTTP request in order to produce an
 * HTTP response.
 */
interface HandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(Context $context, RequestInterface $request): ResponseInterface;
}
