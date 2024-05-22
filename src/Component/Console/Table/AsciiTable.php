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

use Psl\Iter;
use Psl\Str;

/**
 * The `AsciiTable` object with output a human-readable ASCII table in of the
 * provided data.
 */
final class AsciiTable extends AbstractTable
{
    public const array BASIC_CHARACTERS = [
        'top_right_corner' => '+',
        'top_center_corner' => '+',
        'top_left_corner' => '+',
        'center_right_corner' => '+',
        'center_center_corner' => '+',
        'center_left_corner' => '+',
        'bottom_right_corner' => '+',
        'bottom_center_corner' => '+',
        'bottom_left_corner' => '+',
        'line' => '-',
        'header_line' => '=',
        'border' => '|',
        'padding' => ' ',
    ];

    public const array DOUBLE_CHARACTERS = [
        'top_right_corner' => '┐',
        'top_center_corner' => '┬',
        'top_left_corner' => '┌',
        'center_right_corner' => '┤',
        'center_center_corner' => '┼',
        'center_left_corner' => '├',
        'bottom_right_corner' => '┘',
        'bottom_center_corner' => '┴',
        'bottom_left_corner' => '└',
        'line' => '─',
        'header_line' => '═',
        'border' => '│',
        'padding' => ' ',
    ];

    public const array HEAVY_CHARACTERS = [
        'top_right_corner' => '┓',
        'top_center_corner' => '┳',
        'top_left_corner' => '┏',
        'center_right_corner' => '┫',
        'center_center_corner' => '╋',
        'center_left_corner' => '┣',
        'bottom_right_corner' => '┛',
        'bottom_center_corner' => '┻',
        'bottom_left_corner' => '┗',
        'line' => '━',
        'header_line' => '═',
        'border' => '┃',
        'padding' => ' ',
    ];

    private null|string $borderHeaderLine = null;

    /**
     * A dictionary containing necessary characters used for building the border of the table.
     *
     * @var array{
     *      top_right_corner: string,
     *      top_center_corner: string,
     *      top_left_corner: string,
     *      center_right_corner: string,
     *      center_center_corner: string,
     *      center_left_corner: string,
     *      bottom_right_corner: string,
     *      bottom_center_corner: string,
     *      bottom_left_corner: string,
     *      line: string,
     *      header_line: string,
     *      border: string,
     *      padding: string,
     * }
     */
    private array $borderCharacters = [
        'top_right_corner' => '*',
        'top_center_corner' => '*',
        'top_left_corner' => '*',
        'center_right_corner' => '*',
        'center_center_corner' => '*',
        'center_left_corner' => '*',
        'bottom_right_corner' => '*',
        'bottom_center_corner' => '*',
        'bottom_left_corner' => '*',
        'line' => '-',
        'header_line' => '=',
        'border' => '|',
        'padding' => ' ',
    ];

    /**
     * Set the characters used to build the border of the table.
     *
     * @param array{
     *      top_right_corner: string,
     *      top_center_corner: string,
     *      top_left_corner: string,
     *      center_right_corner: string,
     *      center_center_corner: string,
     *      center_left_corner: string,
     *      bottom_right_corner: string,
     *      bottom_center_corner: string,
     *      bottom_left_corner: string,
     *      line: string,
     *      header_line: string,
     *      border: string,
     *      padding: string,
     * } $characters
     */
    public function setBorderCharacters(array $characters): self
    {
        $this->borderCharacters = $characters;

        return $this;
    }

    /**
     * Render the table to the console.
     */
    public function display(): void
    {
        $header = $this->buildRow($this->headers);

        $this->output->writeLine($this->buildBorder(first: true));
        if ($header) {
            $this->output->writeLine($header);
            $this->output->writeLine($this->buildBorder(header: true));
        }


        foreach ($this->rows as $row) {
            $this->output->writeLine($this->buildRow($row));
        }

        $this->output->writeLine($this->buildBorder(last: true));
    }


    /**
     * Build a border for the width of the row width for the class and using the
     * class's `characters`.
     */
    protected function buildBorder(bool $header = false, bool $first = false, bool $last = false): string
    {
        if ($header) {
            if ($this->borderHeaderLine === null) {
                $borderHeaderLine = $this->borderCharacters['center_left_corner'];

                foreach ($this->columnWidths as $k => $width) {
                    $borderHeaderLine .= $this->borderCharacters['padding'];
                    $borderHeaderLine .= Str\repeat($this->borderCharacters['header_line'], $width);
                    $borderHeaderLine .= $this->borderCharacters['padding'];

                    if ($k === Iter\count($this->columnWidths) - 1) {
                        $borderHeaderLine .= $this->borderCharacters['center_right_corner'];
                    } else {
                        $borderHeaderLine .= $this->borderCharacters['center_center_corner'];
                    }
                }

                $this->borderHeaderLine = $borderHeaderLine;
            }

            return $this->borderHeaderLine;
        }

        $prefix = match (true) {
            $first => 'top_',
            $last => 'bottom_',
            default => 'center_',
        };

        $borderLine = $this->borderCharacters["{$prefix}left_corner"];

        foreach ($this->columnWidths as $k => $width) {
            $borderLine .= $this->borderCharacters['padding'];
            $borderLine .= Str\repeat($this->borderCharacters['line'], $width);
            $borderLine .= $this->borderCharacters['padding'];

            if ($k === Iter\count($this->columnWidths) - 1) {
                $borderLine .= $this->borderCharacters["{$prefix}right_corner"];
            } else {
                $borderLine .= $this->borderCharacters["{$prefix}center_corner"];
            }
        }

        return $borderLine;
    }

    /**
     * Build a single cell of the table given the data and the key of the column
     * the data should go into.
     */
    protected function buildCell(string $value, int $key): string
    {
        $width = $this->columnWidths[$key];
        $value = Str\pad_right($value, $width);

        return $this->borderCharacters['padding'] . $value . $this->borderCharacters['padding'];
    }

    /**
     * Given a container of data, build a single row of the table.
     *
     * @param list<string> $data
     */
    protected function buildRow(array $data): string
    {
        if (Iter\is_empty($data)) {
            return '';
        }

        $row = [];
        foreach ($data as $index => $value) {
            $row[] = $this->buildCell($value, $index);
        }

        return $this->borderCharacters['border'] . Str\join($row, $this->borderCharacters['border']) . $this->borderCharacters['border'];
    }
}
