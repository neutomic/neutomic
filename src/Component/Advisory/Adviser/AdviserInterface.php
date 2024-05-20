<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\Adviser;

use Neu\Component\Advisory\Advice;

/**
 * Interface for an adviser that provides a single piece of advice.
 */
interface AdviserInterface
{
    /**
     * Retrieve an advice instance.
     *
     * @return Advice|null An instance of Advice, or null if no advice is available.
     */
    public function getAdvice(): ?Advice;
}
