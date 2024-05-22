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

trait BlockFactoryTrait
{
    /**
     * Create a new success block.
     */
    public function createSuccessBlock(OutputInterface $output): SuccessBlock
    {
        return new SuccessBlock($output);
    }

    /**
     * Create a new section block.
     */
    public function createSectionBlock(OutputInterface $output): SectionBlock
    {
        return new SectionBlock($output);
    }

    /**
     * Create a new title block.
     */
    public function createTitleBlock(OutputInterface $output): TitleBlock
    {
        return new TitleBlock($output);
    }

    /**
     * Create a new text block.
     */
    public function createTextBlock(OutputInterface $output): TextBlock
    {
        return new TextBlock($output);
    }

    /**
     * Create a new note block.
     */
    public function createNoteBlock(OutputInterface $output): NoteBlock
    {
        return new NoteBlock($output);
    }

    /**
     * Create a new warning block.
     */
    public function createWarningBlock(OutputInterface $output): WarningBlock
    {
        return new WarningBlock($output);
    }

    /**
     * Create a new caution block.
     */
    public function createCautionBlock(OutputInterface $output): CautionBlock
    {
        return new CautionBlock($output);
    }

    /**
     * Create a new error block.
     */
    public function createErrorBlock(OutputInterface $output): ErrorBlock
    {
        return new ErrorBlock($output);
    }
}
