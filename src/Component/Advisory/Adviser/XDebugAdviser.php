<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\Adviser;

use Neu\Component\Advisory\Advice;

use function extension_loaded;

/**
 * Adviser that provides advice on disabling the Xdebug extension.
 */
final readonly class XDebugAdviser implements AdviserInterface
{
    /**
     * Retrieve an advice instance regarding the Xdebug extension.
     *
     * @return Advice|null An instance of Advice if Xdebug is enabled, or null if it is disabled.
     */
    public function getAdvice(): ?Advice
    {
        if (!extension_loaded('xdebug')) {
            return null;
        }

        return Advice::forPerformance(
            'Disable Xdebug',
            'Xdebug should be disabled in production environments to improve PHP performance.',
            'Disable the "xdebug" extension in the PHP configuration file.'
        );
    }
}
