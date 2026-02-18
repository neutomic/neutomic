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

namespace Neu\Component\Advisory;

/**
 * Enum representing different categories of advice.
 */
enum AdviceCategory: string
{
    /**
     * Advice related to security.
     */
    case Security = 'security';

    /**
     * Advice related to performance.
     */
    case Performance = 'performance';

    /**
     * Advice related to maintainability.
     */
    case Maintainability = 'maintainability';

    /**
     * Advice related to usability.
     */
    case Usability = 'usability';

    /**
     * Advice related to accessibility.
     */
    case Accessibility = 'accessibility';

    /**
     * Advice that falls into other categories not specified.
     */
    case Other = 'other';
}
