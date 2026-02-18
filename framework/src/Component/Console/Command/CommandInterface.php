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

namespace Neu\Component\Console\Command;

use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;

interface CommandInterface
{
    /**
     * The method that stores the code to be executed when the command is run.
     */
    public function run(InputInterface $input, OutputInterface $output): ExitCode|int;
}
