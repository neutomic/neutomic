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
use Override;

use function extension_loaded;

/**
 * Adviser that provides advice on disabling the Datadog Trace extension.
 */
final readonly class DatadogTraceAdviser implements AdviserInterface
{
    /**
     * Retrieve an advice instance regarding the Datadog Trace extension.
     *
     * @return Advice|null An instance of Advice if Datadog Trace is enabled, or null if it is disabled.
     */
    #[Override]
    public function getAdvice(): null|Advice
    {
        if (!extension_loaded('datadog_trace')) {
            return null;
        }

        return Advice::forPerformance(
            'Disable Datadog Trace',
            'Datadog Trace should be disabled in production environments to improve PHP performance.',
            'Disable the "datadog_trace" extension in the PHP configuration file.'
        );
    }
}
