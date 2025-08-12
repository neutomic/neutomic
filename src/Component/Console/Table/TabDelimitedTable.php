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

use Neu\Component\Console\Output\OutputInterface;
use Psl\Str;

/**
 * The `TabDelimitedTable` class builds and outputs a table with values tab-delimited
 * for use in other applications.
 */
final class TabDelimitedTable extends AbstractTable
{
    /**
     * Display the table in the console.
     */
    #[\Override]
    public function display(): void
    {
        $this->output->writeLine(Str\join($this->headers, OutputInterface::TAB));

        foreach ($this->rows as $row) {
            $this->output->writeLine(Str\join($row, OutputInterface::TAB));
        }
    }
}
