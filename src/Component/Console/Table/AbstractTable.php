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
use Neu\Component\Console\Output\Type;
use Psl\Iter;
use Psl\Str;

/**
 * The `AbstractTable` class provides the core functionality for building and
 * displaying tabular data.
 */
abstract class AbstractTable implements TableInterface
{
    /**
     * Data structure that holds the width of each column.
     *
     * @var array<int, int<0, max>>
     */
    protected array $columnWidths = [];

    /**
     * Data structure holding the header names of each column.
     *
     * @var list<string>
     */
    protected array $headers = [];

    /**
     * Data structure holding the data for each row in the table.
     *
     * @var list<list<string>>
     */
    protected array $rows = [];

    public function __construct(
        protected readonly OutputInterface $output,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function setHeaders(array $headers): self
    {
        $this->setColumnWidths($headers);
        $this->headers = $headers;

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @param list<list<string>> $rows
     */
    public function setRows(array $rows): self
    {
        $this->rows = [];
        foreach ($rows as $row) {
            $this->addRow($row);
        }

        return $this;
    }

    /**
     * Append a new row of data to the end of the existing rows.
     *
     * @param list<string> $row
     */
    public function addRow(array $row): self
    {
        $this->setColumnWidths($row);
        $this->rows[] = $row;

        return $this;
    }

    /**
     * Given the row of data, adjust the column width accordingly so that the
     * columns' width is that of the maximum data field size.
     *
     * @param list<string> $row
     */
    protected function setColumnWidths(array $row): self
    {
        foreach ($row as $index => $value) {
            $width = $this->lengthWithoutDecoration($value);
            $currentWidth = $this->columnWidths[$index] ?? 0;

            if ($width > $currentWidth) {
                if (Iter\count($this->columnWidths) === $index) {
                    $this->columnWidths[] = $width;
                } else {
                    $this->columnWidths[$index] = $width;
                }
            }
        }

        return $this;
    }

    /**
     * Given a string, return the length of the string without any decoration.
     *
     * @param string $string
     *
     * @return int<0, max>
     */
    private function lengthWithoutDecoration(string $string): int
    {
        /** @var int<0, max> */
        return Str\length($this->output->format($string, Type::Plain));
    }
}
