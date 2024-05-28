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

namespace Neu\Component\Http\Runtime\Exception;

use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Runtime\ContentDelivery\ContentDeliverer;

/**
 * Represents an exception that is thrown when an error occurs while interacting with the filesystem.
 *
 * @see ContentDeliverer::deliver() for more information.
 */
final class FilesystemException extends RuntimeException
{
}
