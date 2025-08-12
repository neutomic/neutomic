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

use function ini_get;

/**
 * Adviser that provides advice on PHP assertions configuration.
 */
final readonly class AssertationAdviser implements AdviserInterface
{
    /**
     * Retrieve an advice instance regarding assertions.
     *
     * @return Advice|null An instance of Advice if assertions are not disabled, or null if they are.
     */
    #[Override]
    public function getAdvice(): null|Advice
    {
        $configuration = ini_get('zend.assertions');
        if ($configuration === '-1') {
            return null;
        }

        return Advice::forPerformance(
            'Disable assertions in production',
            'Assertions are a debugging feature that should be disabled in production environments to improve performance.',
            'Disable assertions in the PHP configuration file or set the `zend.assertions` directive to `-1`.'
        );
    }
}
