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

use InvalidArgumentException;

/**
 * Exception thrown when parameters are passed in the input that do not belong
 * to registered input definitions.
 */
final class InvalidNumberOfArgumentsException extends InvalidArgumentException implements ExceptionInterface
{
}
