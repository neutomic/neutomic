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

namespace Neu\Component\Console\Table;

/**
 * A `Table` object will construct the markup for a human-readable (or otherwise
 * parsable) representation of tabular data.
 */
interface TableInterface
{
    /**
     * Set the column names for the table.
     *
     * @param list<string> $headers
     */
    public function setHeaders(array $headers): self;

    /**
     * Set the data for the rows in the table with A vector containing a vec
     * for each row in the table.
     *
     * @param list<list<string>> $rows
     */
    public function setRows(array $rows): self;

    /**
     * Add a row of data to the end of the existing data.
     *
     * @param list<string> $row
     */
    public function addRow(array $row): self;

    /**
     * Render the table to the console.
     */
    public function display(): void;
}
