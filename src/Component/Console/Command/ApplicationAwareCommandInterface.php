<?php

declare(strict_types=1);

namespace Neu\Component\Console\Command;

use Neu\Component\Console\ApplicationInterface;

interface ApplicationAwareCommandInterface extends CommandInterface
{
    public function setApplication(ApplicationInterface $application): void;
}
