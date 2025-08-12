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
use Psl\Str;

use function ini_get;

/**
 * Adviser that provides advice on PHP max execution time settings.
 */
final readonly class MaxExecutionTimeAdviser implements AdviserInterface
{
    /**
     * Retrieve an advice instance regarding the max execution time setting.
     *
     * @return Advice|null An instance of Advice if the max execution time is not set to unlimited, or null if it is set correctly.
     */
    #[\Override]
    public function getAdvice(): null|Advice
    {
        $maxExecutionTime = (string) ini_get('max_execution_time');
        $maxExecutionTime = Str\to_int($maxExecutionTime);
        if ($maxExecutionTime !== 0) { // Check if max execution time is not set to unlimited
            return Advice::forPerformance(
                'Set PHP Max Execution Time to Unlimited',
                'The current PHP max execution time is limited, which can interrupt execution.',
                'Set the "max_execution_time" to 0 in your php.ini configuration file or use `set_time_limit(0);` at the beginning of your script.'
            );
        }

        return null;
    }
}
