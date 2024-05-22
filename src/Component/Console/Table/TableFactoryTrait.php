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

trait TableFactoryTrait
{
    /**
     * Get an instance of the {@see AsciiTable} class.
     *
     * @param list<string> $headers
     * @param list<list<string>> $rows
     */
    protected function createAsciiTable(OutputInterface $output, array $headers, array $rows = []): AsciiTable
    {
        $table = new AsciiTable($output);
        $table->setHeaders($headers);
        $table->setRows($rows);

        return $table;
    }

    /**
     * Get an instance of the {@see TabDelimitedTable} class.
     *
     * @param list<string> $headers
     * @param list<list<string>> $rows
     */
    protected function createTabDelimitedTable(OutputInterface $output, array $headers, array $rows = []): TabDelimitedTable
    {
        $table = new TabDelimitedTable($output);
        $table->setHeaders($headers);
        $table->setRows($rows);

        return $table;
    }
}
