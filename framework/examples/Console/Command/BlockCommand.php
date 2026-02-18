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
use Neu\Component\Console\Block\BlockFactoryTrait;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;

#[Command('block', 'Example of using blocks')]
final readonly class BlockCommand implements CommandInterface
{
    use BlockFactoryTrait;

    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $this->createSuccessBlock($output)->display('This is a success block');
        $this->createSectionBlock($output)->display('This is a section block');
        $this->createTitleBlock($output)->display('This is a title block');
        $this->createTextBlock($output)->display('This is a text block');
        $this->createNoteBlock($output)->display('This is a note block');
        $this->createWarningBlock($output)->display('This is a warning block');
        $this->createCautionBlock($output)->display('This is a caution block');
        $this->createErrorBlock($output)->display('This is an error block');

        return 0;
    }
}
