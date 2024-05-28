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

namespace Neu\Component\Http\Recovery;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Throwable;

/**
 * Interface for implementing a recovery strategy for handling exceptions
 * that occur during HTTP request processing.
 *
 * This interface defines a method for recovering from exceptions and generating
 * an appropriate HTTP response. Implementations of this interface should handle
 * the exceptions and provide a response that can be returned to the client.
 */
interface RecoveryInterface
{
    /**
     * Recover from an exception that occurred during HTTP request processing.
     *
     * This method is called when an exception is thrown during the handling of
     * an HTTP request. Implementations should handle the exception and return
     * a suitable HTTP response.
     *
     * @param Context $context The context in which the exception occurred.
     * @param RequestInterface $request The HTTP request being processed when the exception occurred.
     * @param Throwable $throwable The exception that was thrown.
     *
     * @return ResponseInterface The HTTP response generated after handling the exception.
     */
    public function recover(Context $context, RequestInterface $request, Throwable $throwable): ResponseInterface;
}
