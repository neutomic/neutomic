<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\Adviser;

use Neu\Component\Advisory\Advice;
use Revolt\EventLoop;

/**
 * Adviser that provides advice on disabling event loop tracing.
 */
final readonly class EventLoopTracingAdviser implements AdviserInterface
{
    /**
     * Retrieve an advice instance regarding event loop tracing.
     *
     * @return Advice|null An instance of Advice if event loop tracing is enabled, or null if it is disabled.
     */
    public function getAdvice(): ?Advice
    {
        $driver = EventLoop::getDriver();
        if ($driver instanceof EventLoop\Driver\TracingDriver) {
            return Advice::forPerformance(
                'Disable Event Loop Tracing',
                'Event loop tracing should be disabled in production environments to improve performance.',
                'Remove the "REVOLT_DRIVER_DEBUG_TRACE" environment variable or set it to an empty value.'
            );
        }

        return null;
    }
}
