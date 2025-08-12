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
use Neu\Component\Http\Server\Event\ServerStartedEvent;
use Psr\Log\LoggerInterface;
use Override;

/**
 * @implements ListenerInterface<BeforeExecuteEvent>
 */
#[Listener(ServerStartedEvent::class)]
final readonly class ServerStartedEventListener implements ListenerInterface
{
    private ProjectMode $mode;
    private AdvisoryInterface $advisory;
    private LoggerInterface $logger;

    public function __construct(ProjectMode $mode, AdvisoryInterface $advisory, LoggerInterface $logger)
    {
        $this->mode = $mode;
        $this->advisory = $advisory;
        $this->logger = $logger;
    }

    /**
     * Display advisory messages before executing the command.
     *
     * @param BeforeExecuteEvent $event
     *
     * @return BeforeExecuteEvent
     */
    #[Override]
    public function process(object $event): object
    {
        if ($this->mode->isProduction()) {
            $advices = $this->advisory->getAdvices();

            foreach ($advices as $advice) {
                $this->logger->warning('[advice][{category}] {message} - {solution}', [
                    'category' => $advice->category->value,
                    'message' => $advice->message,
                    'description' => $advice->description,
                    'solution' => $advice->solution,
                ]);
            }
        }

        return $event;
    }
}
