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

use Neu\Component\Console\Output\OutputInterface;

final readonly class NoteBlock extends Block
{
    public function __construct(OutputInterface $output)
    {
        parent::__construct($output, 'NOTE', 'comment', ' ! ');
    }
}
