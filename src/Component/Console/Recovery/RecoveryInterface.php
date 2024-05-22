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

namespace Neu\Component\Console\Recovery;

use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Throwable;

interface RecoveryInterface
{
    /**
     * Recover from the given throwable and return the proper exit code.
     */
    public function recover(InputInterface $input, OutputInterface $output, Throwable $throwable): int;
}
