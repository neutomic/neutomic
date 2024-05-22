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

use function ini_get;

/**
 * Adviser that provides advice on PHP memory limit settings.
 */
final readonly class MemoryLimitAdviser implements AdviserInterface
{
    private const int MEMORY_LIMIT_THRESHOLD = 128 * 1024 * 1024;

    /**
     * Retrieve an advice instance regarding the memory limit setting.
     *
     * @return Advice|null An instance of Advice if the memory limit is set too low, or null if it is set appropriately.
     */
    public function getAdvice(): null|Advice
    {
        $memoryLimit = (string) ini_get('memory_limit');
        $memoryLimit = Internal\Utility::parseValue($memoryLimit);

        if ($memoryLimit !== -1 && $memoryLimit < self::MEMORY_LIMIT_THRESHOLD) {
            return Advice::forPerformance(
                'Increase PHP Memory Limit',
                'The current PHP memory limit ( ' . ((string) $memoryLimit) . ' ) is set too low, which can cause issues with memory-intensive operations.',
                'Increase the memory limit in your php.ini configuration file to at least 128MB or set it to -1 for unlimited memory.'
            );
        }

        return null;
    }
}
