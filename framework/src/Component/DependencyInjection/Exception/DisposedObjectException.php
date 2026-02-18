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

namespace Neu\Component\DependencyInjection\Exception;

use Neu\Component\DependencyInjection\DisposableInterface;

/**
 * An exception that is thrown when attempting to call a method on a disposed object.
 */
final class DisposedObjectException extends RuntimeException
{
    /**
     * A method to guard against calling a method on a disposed object.
     *
     * If the given disposable object is disposed, this method will throw a new {@see DisposedObjectException}.
     */
    public static function guard(DisposableInterface $object): void
    {
        if ($object->isDisposed()) {
            /** @psalm-suppress MissingThrowsDocblock */
            throw self::create();
        }
    }

    /**
     * A method to create a new {@see DisposedObjectException} instance to be thrown
     * for when attempting to call a method on a disposed object.
     */
    public static function create(): self
    {
        return new self('The object has been disposed and cannot be used.');
    }
}
