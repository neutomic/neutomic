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
use Revolt\EventLoop\Driver\EvDriver;
use Revolt\EventLoop\Driver\EventDriver;
use Revolt\EventLoop\Driver\StreamSelectDriver;
use Revolt\EventLoop\Driver\UvDriver;

/**
 * Adviser that provides advice on using and installing ext-uv, ext-event, or ext-ev for better performance.
 */
final readonly class EventLoopDriverAdviser implements AdviserInterface
{
    /**
     * Retrieve an advice instance regarding event loop extensions.
     *
     * @return Advice|null An instance of Advice if the StreamSelectDriver is used and no high-performance extensions are installed, or advice to switch the driver if high-performance extensions are installed.
     */
    public function getAdvice(): null|Advice
    {
        $driver = EventLoop::getDriver();

        if ($driver instanceof StreamSelectDriver) {
            if (EvDriver::isSupported()) {
                return Advice::forPerformance(
                    'Switch to "' . EvDriver::class . '" driver',
                    'The "' . EvDriver::class . '" driver is supported. For better performance, use it instead of the "' . StreamSelectDriver::class . '" driver.',
                    'Remove the "REVOLT_DRIVER" environment variable to allow the system to automatically switch to "' . EvDriver::class . '".'
                );
            }

            if (UvDriver::isSupported()) {
                return Advice::forPerformance(
                    'Switch to "' . UvDriver::class . '" driver',
                    'The "' . UvDriver::class . '" driver is supported. For better performance, use it instead of the "' . StreamSelectDriver::class . '" driver.',
                    'Remove the "REVOLT_DRIVER" environment variable to allow the system to automatically switch to "' . UvDriver::class . '".'
                );
            }

            if (EventDriver::isSupported()) {
                return Advice::forPerformance(
                    'Switch to "' . EventDriver::class . '" driver',
                    'The "' . EventDriver::class . '" driver is supported. For better performance, use it instead of the "' . StreamSelectDriver::class . '" driver.',
                    'Remove the "REVOLT_DRIVER" environment variable to allow the system to automatically switch to "' . EventDriver::class . '".'
                );
            }

            return Advice::forPerformance(
                'Install ext-uv, ext-event, or ext-ev',
                'For better performance, install ext-uv, ext-event, or ext-ev. The "' . StreamSelectDriver::class .  '" is a fallback and not optimal for production environments.',
                'Install one of the mentioned extensions and ensure it is enabled in your PHP configuration.'
            );
        }

        return null;
    }
}
