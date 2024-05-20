<?php

declare(strict_types=1);

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
    public function display(): void
    {
        $this->output->writeLine(Str\join($this->headers, OutputInterface::TAB));

        foreach ($this->rows as $row) {
            $this->output->writeLine(Str\join($row, OutputInterface::TAB));
        }
    }
}
