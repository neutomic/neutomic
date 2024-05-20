<?php

declare(strict_types=1);

namespace Neu\Component\Advisory;

use Neu\Component\Advisory\Adviser\AdviserInterface;

/**
 * The {@see AdvisoryInterface} is responsible for providing advice to the user based on the current state of the system.
 */
interface AdvisoryInterface
{
    /**
     * Add an adviser to the advisory system.
     */
    public function addAdviser(AdviserInterface $adviser): void;

    /**
     * Get a list of all the advice provided by the advisory system.
     *
     * @return list<Advice> The list of advice provided by the advisory system.
     */
    public function getAdvices(): array;
}
