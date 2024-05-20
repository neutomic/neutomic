<?php

declare(strict_types=1);

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
