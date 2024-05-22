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
    public function getAdvice(): null|Advice
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
