<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\Adviser;

use Neu\Component\Advisory\Advice;

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
    public function getAdvice(): ?Advice
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
