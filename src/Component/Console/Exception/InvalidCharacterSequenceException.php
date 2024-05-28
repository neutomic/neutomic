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

namespace Neu\Component\Console\Exception;

use Neu\Component\Console\Feedback\FeedbackInterface;

/**
 * Exception thrown when an invalid character sequence is used in a {@see FeedbackInterface}
 * class.
 */
final class InvalidCharacterSequenceException extends RuntimeException implements ExceptionInterface
{
}
