<?php

declare(strict_types=1);

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
