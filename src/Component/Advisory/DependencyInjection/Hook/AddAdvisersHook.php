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

namespace Neu\Component\Advisory\DependencyInjection\Hook;

use Neu\Component\Advisory\Adviser\AdviserInterface;
use Neu\Component\Advisory\AdvisoryInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\HookInterface;

/**
 * A hook to add advisers to the advisory.
 */
final readonly class AddAdvisersHook implements HookInterface
{
    /**
     * The advisory service identifier.
     *
     * @var non-empty-string
     */
    private string $advisory;

    /**
     * Create a new advisers hook.
     *
     * @param non-empty-string|null $advisory The advisory service identifier.
     */
    public function __construct(null|string $advisory = null)
    {
        $this->advisory = $advisory ?? AdvisoryInterface::class;
    }

    /**
     * @throws ExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): void
    {
        $advisory = $container->getTyped($this->advisory, AdvisoryInterface::class);

        foreach ($container->getInstancesOf(AdviserInterface::class) as $adviser) {
            $advisory->addAdviser($adviser);
        }
    }
}
