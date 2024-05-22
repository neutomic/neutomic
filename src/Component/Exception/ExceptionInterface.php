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

namespace Neu\Component\Exception;

use Throwable;

/**
 * A marker interface for exceptions in the contract namespace.
 *
 * All exceptions in the contract namespace should implement this interface.
 */
interface ExceptionInterface extends Throwable
{
}
