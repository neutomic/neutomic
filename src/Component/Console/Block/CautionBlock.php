<?php

declare(strict_types=1);

namespace Neu\Component\Console\Block;

use Neu\Component\Console\Output\OutputInterface;

final readonly class CautionBlock extends Block
{
    public function __construct(OutputInterface $output)
    {
        parent::__construct($output, 'CAUTION', 'error', ' ! ', true);
    }
}
