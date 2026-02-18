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
