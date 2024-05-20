<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\Listener;

use Neu\Component\Advisory\AdvisoryInterface;
use Neu\Component\Advisory\Internal\ConsoleTrait;
use Neu\Component\Console\Event\BeforeExecuteEvent;
use Neu\Component\DependencyInjection\ProjectMode;
use Neu\Component\EventDispatcher\Attribute\Listener;
use Neu\Component\EventDispatcher\Listener\ListenerInterface;

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
