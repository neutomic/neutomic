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

namespace Neu\Component\Advisory\Internal;

use Neu\Component\Advisory\Advice;
use Neu\Component\Console\Block\BlockFactoryTrait;
use Neu\Component\Console\Output\OutputInterface;
use Psl\Str;

/**
 * Trait to display advisory messages in the console.
 *
 * @internal
 */
trait ConsoleTrait
{
    use BlockFactoryTrait;

    /**
     * Display the given advice.
     */
    protected function display(OutputInterface $output, Advice $advice): void
    {
        $block = $this->createCautionBlock($output);

        /** @var non-empty-string $type */
        $type = Str\uppercase($advice->category->value);
        $block = $block->withType($type);

        $message = $advice->message;
        if ('' !== $advice->description) {
            $message .= OutputInterface::END_OF_LINE;
            $message .= OutputInterface::END_OF_LINE;
            $message .= $advice->description;
        }

        if ('' !== $advice->solution) {
            $message .= OutputInterface::END_OF_LINE;
            $message .= OutputInterface::END_OF_LINE;
            $message .= OutputInterface::END_OF_LINE;
            $message .= 'Solution: ' . $advice->solution;
        }

        $block->display($message);
    }
}
