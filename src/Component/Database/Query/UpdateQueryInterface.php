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

namespace Neu\Component\Database\Query;

interface UpdateQueryInterface extends WhereQueryInterface
{
    /**
     * Sets a new value for a column.
     *
     * @param non-empty-string $column The column to set.
     * @param non-empty-string $value The value, expression, placeholder, etc.
     */
    public function set(string $column, string $value): static;
}
