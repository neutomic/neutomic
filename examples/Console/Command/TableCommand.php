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

namespace Neu\Examples\Console\Command;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;
use Neu\Component\Console\Table\AsciiTable;
use Neu\Component\Console\Table\TableFactoryTrait;

#[Command('table', 'Example of using tables.')]
final readonly class TableCommand implements CommandInterface
{
    use TableFactoryTrait;

    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $headers = ['Name', 'Email', 'Password', 'Role'];
        $rows = [
            ['John Doe', 'john.doe@example.com', 'password', '<fg=red>Admin</>'],
            ['Jane Doe', 'jane.doe@example.com', 'password', 'User'],
            ['Joe Bloggs', 'joe.bloggs@example.com', 'password', 'User'],
        ];

        $this->createAsciiTable($output, $headers, $rows)->display();
        $this->createAsciiTable($output, [], $rows)->display();
        $this->createAsciiTable($output, $headers, $rows)->setBorderCharacters(AsciiTable::DOUBLE_CHARACTERS)->display();
        $this->createAsciiTable($output, $headers, $rows)->setBorderCharacters(AsciiTable::HEAVY_CHARACTERS)->display();

        $this->createAsciiTable($output, $headers, $rows)->setBorderCharacters([
            'top_right_corner' => '<fg=yellow>┐</>',
            'top_center_corner' => '<fg=yellow>┬</>',
            'top_left_corner' => '<fg=yellow>┌</>',
            'center_right_corner' => '<fg=yellow>┤</>',
            'center_center_corner' => '<fg=yellow>┼</>',
            'center_left_corner' => '<fg=yellow>├</>',
            'bottom_right_corner' => '<fg=yellow>┘</>',
            'bottom_center_corner' => '<fg=yellow>┴</>',
            'bottom_left_corner' => '<fg=yellow>└</>',
            'line' => '<fg=bright-red>─</>',
            'header_line' => '<fg=bright-red>═</>',
            'border' => '<fg=bright-red>│</>',
            'padding' => ' ',
        ])->display();

        $this->createTabDelimitedTable($output, ['A','V'], [['01', 'X'], ['02', 'Y'], ['03', 'W']])->display();

        return 0;
    }
}
