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

namespace Neu\Framework\Listener\Advisory;

use Neu\Component\Advisory\AdvisoryInterface;
use Neu\Component\Console\Event\BeforeExecuteEvent;
use Neu\Component\DependencyInjection\ProjectMode;
use Neu\Component\EventDispatcher\Attribute\Listener;
use Neu\Component\EventDispatcher\Listener\ListenerInterface;
use Neu\Framework\Internal\Advisory\ConsoleTrait;

/**
 * @implements ListenerInterface<BeforeExecuteEvent>
 */
#[Listener(BeforeExecuteEvent::class)]
final readonly class BeforeExecuteEventListener implements ListenerInterface
{
    use ConsoleTrait;

    private ProjectMode $mode;
    private AdvisoryInterface $advisory;

    public function __construct(ProjectMode $mode, AdvisoryInterface $advisory)
    {
        $this->mode = $mode;
        $this->advisory = $advisory;
    }

    /**
     * Display advisory messages before executing the command.
     *
     * @param BeforeExecuteEvent $event
     *
     * @return BeforeExecuteEvent
     */
    public function process(object $event): object
    {
        if ($this->mode->isProduction()) {
            $advices = $this->advisory->getAdvices();

            foreach ($advices as $advice) {
                $this->display($event->output, $advice);
            }
        }

        return $event;
    }
}
