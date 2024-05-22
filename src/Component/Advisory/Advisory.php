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
use Psr\Log\LoggerInterface;

final class Advisory implements AdvisoryInterface
{
    /**
     * The logger to use for logging.
     *
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;

    /**
     * The advisers that will provide advices.
     *
     * @var list<AdviserInterface>
     */
    private array $advisers;

    /**
     * Create a new {@see Advisory} instance.
     *
     * @param LoggerInterface $logger The logger to use for logging.
     * @param list<AdviserInterface> $advisers The advisers that will provide advices.
     */
    public function __construct(LoggerInterface $logger, array $advisers = [])
    {
        $this->advisers = $advisers;
        $this->logger = $logger;
    }

    public function addAdviser(AdviserInterface $adviser): void
    {
        $this->advisers[] = $adviser;
    }

    /**
     * @inheritDoc
     */
    public function getAdvices(): array
    {
        $advices = [];
        foreach ($this->advisers as $adviser) {
            $advice = $adviser->getAdvice();

            if ($advice !== null) {
                $this->logger->info('Adviser "{adviser}" provided an advice of category "{category}": {message}', [
                    'adviser' => $adviser::class,
                    'category' => $advice->category,
                    'message' => $advice->message,
                ]);

                $advices[] = $advice;
            } else {
                $this->logger->info('Adviser "{adviser}" did not provide an advice.', [
                    'adviser' => $adviser::class,
                ]);
            }
        }

        return $advices;
    }
}
