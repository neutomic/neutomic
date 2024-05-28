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

/**
 * Exception thrown when a value is required and not present. This can be the
 * case with options or arguments.
 */
final class MissingValueException extends RuntimeException implements ExceptionInterface
{
}
