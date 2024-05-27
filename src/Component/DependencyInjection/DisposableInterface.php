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

namespace Neu\Component\DependencyInjection;

interface DisposableInterface
{
    /**
     * Check if the object has been disposed.
     */
    public function isDisposed(): bool;

    /**
     * Dispose of the object.
     *
     * After this method is called, the object should be considered unusable,
     * and any references to it should be removed.
     *
     * Any further use of the object should throw a {@see Exception\DisposedObjectException}.
     */
    public function dispose(): void;
}
