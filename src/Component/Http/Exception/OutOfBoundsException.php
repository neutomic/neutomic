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

namespace Neu\Component\Http\Exception;

use Neu\Component\Exception\OutOfBoundsException as RootOutOfBoundsException;

final class OutOfBoundsException extends RootOutOfBoundsException implements ExceptionInterface
{
}
