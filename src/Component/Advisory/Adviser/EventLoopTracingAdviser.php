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
    #[\Override]
    public function getAdvice(): null|Advice
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
