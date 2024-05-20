<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\Adviser;

use Neu\Component\Advisory\Advice;

use function extension_loaded;

/**
 * Adviser that provides advice on disabling the PCov extension.
 */
final readonly class PCovAdviser implements AdviserInterface
{
    /**
     * Retrieve an advice instance regarding the PCov extension.
     *
     * @return Advice|null An instance of Advice if PCov is enabled, or null if it is disabled.
     */
    public function getAdvice(): ?Advice
    {
        if (!extension_loaded('pcov')) {
            return null;
        }

        return Advice::forPerformance(
            'Disable PCov',
            'PCov should be disabled in production environments to improve PHP performance.',
            'Disable the "pcov" extension in the PHP configuration file.'
        );
    }
}
