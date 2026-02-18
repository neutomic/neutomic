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

namespace Neu\Component\DependencyInjection\Trait;

use Neu\Component\DependencyInjection\DisposableInterface;

/**
 * A trait to provide convenience methods for disposable objects.
 *
 * @psalm-require-implements DisposableInterface
 */
trait DisposableConvenienceMethodsTrait
{
    /**
     * A flag to indicate if the object has been disposed.
     */
    protected bool $disposed = false;

    /**
     * @inheritDoc
     */
    final public function isDisposed(): bool
    {
        return $this->disposed;
    }

    /**
     * @inheritDoc
     */
    abstract public function dispose(): void;

    /**
     * Disposes of the object when it is destroyed.
     */
    final public function __destruct()
    {
        if ($this->disposed) {
            return;
        }

        $this->dispose();
    }
}
