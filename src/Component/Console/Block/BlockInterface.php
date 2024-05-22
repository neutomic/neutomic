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

namespace Neu\Component\Console\Block;

use Neu\Component\Console\Output\Verbosity;

interface BlockInterface
{
    /**
     * Display the block with the given messages.
     */
    public function display(string $message, Verbosity $verbosity = Verbosity::Normal): self;
}
