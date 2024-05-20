<?php

declare(strict_types=1);

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

        $block = $block->withType(Str\uppercase($advice->category->value));

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
