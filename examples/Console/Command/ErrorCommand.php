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

use Exception;
use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;

#[Command('error', 'I am not well')]
final readonly class ErrorCommand implements CommandInterface
{
    /**
     * @throws Exception
     */
    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        throw new Exception('I am not well');
    }
}
