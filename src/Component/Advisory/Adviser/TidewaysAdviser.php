<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\Adviser;

use Neu\Component\Advisory\Advice;

use function extension_loaded;

/**
 * Adviser that provides advice on disabling the Tideways extension.
 */
final readonly class TidewaysAdviser implements AdviserInterface
{
    /**
     * Retrieve an advice instance regarding the Tideways extension.
     *
     * @return Advice|null An instance of Advice if Tideways is enabled, or null if it is disabled.
     */
    public function getAdvice(): ?Advice
    {
        if (!extension_loaded('tideways')) {
            return null;
        }

        return Advice::forPerformance(
            'Disable Tideways',
            'Tideways should be disabled in production environments to improve PHP performance.',
            'Disable the "tideways" extension in the PHP configuration file.'
        );
    }
}
