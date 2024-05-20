<?php

declare(strict_types=1);

namespace Neu\Component\Console\Command;

use Neu\Component\Console\ApplicationInterface;

/**
 * @require-implements ApplicationAwareCommandInterface
 */
trait ApplicationAwareCommandTrait
{
    /**
     * The {@see ApplicationInterface} that is currently running the command.
     */
    protected readonly ApplicationInterface $application;

    public function setApplication(ApplicationInterface $application): void
    {
        $this->application = $application;
    }
}
