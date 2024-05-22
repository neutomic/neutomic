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
    public function getAdvice(): null|Advice;
}
