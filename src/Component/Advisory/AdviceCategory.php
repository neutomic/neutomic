<?php

declare(strict_types=1);

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
