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

use Neu\Component\Advisory\Adviser\AdviserInterface;
use Override;

final class Advisory implements AdvisoryInterface
{
    /**
     * The advisers that will provide advices.
     *
     * @var list<AdviserInterface>
     */
    private array $advisers;

    /**
     * Create a new {@see Advisory} instance.
     *
     * @param list<AdviserInterface> $advisers The advisers that will provide advices.
     */
    public function __construct(array $advisers = [])
    {
        $this->advisers = $advisers;
    }

    #[Override]
    public function addAdviser(AdviserInterface $adviser): void
    {
        $this->advisers[] = $adviser;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getAdvices(): array
    {
        $advices = [];
        foreach ($this->advisers as $adviser) {
            $advice = $adviser->getAdvice();

            if ($advice !== null) {
                $advices[] = $advice;
            }
        }

        return $advices;
    }
}
