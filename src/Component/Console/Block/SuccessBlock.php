<?php

declare(strict_types=1);

namespace Neu\Component\Console\Block;

use Neu\Component\Console\Output\OutputInterface;

final readonly class SuccessBlock extends Block
{
    public function __construct(OutputInterface $output)
    {
        parent::__construct($output, 'OK', 'fg=black;bg=bright-green', '   ', true);
    }
}
