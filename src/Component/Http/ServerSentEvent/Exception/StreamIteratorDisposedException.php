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

namespace Neu\Component\Http\ServerSentEvent\Exception;

use Neu\Component\Http\Exception\RuntimeException;

/**
 * Thrown when attempting to send an event to a stream whose iterator has been disposed.
 */
final class StreamIteratorDisposedException extends RuntimeException implements ExceptionInterface
{
}
