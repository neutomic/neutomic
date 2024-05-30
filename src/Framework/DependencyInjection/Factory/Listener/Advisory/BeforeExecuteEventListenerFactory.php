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

namespace Neu\Framework\DependencyInjection\Factory\Listener\Advisory;

use Neu\Component\Advisory\AdvisoryInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Framework\Listener\Advisory\BeforeExecuteEventListener;

/**
 * Factory for creating a {@see BeforeExecuteEventListener} instance.
 *
 * @implements FactoryInterface<BeforeExecuteEventListener>
 */
final readonly class BeforeExecuteEventListenerFactory implements FactoryInterface
{
    /**
     * The advisory service to use.
     *
     * @var non-empty-string
     */
    private string $advisory;

    /**
     * @param null|non-empty-string $advisory The advisory service to use.
     */
    public function __construct(null|string $advisory = null)
    {
        $this->advisory = $advisory ?? AdvisoryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): BeforeExecuteEventListener
    {
        return new BeforeExecuteEventListener(
            $container->getProject()->mode,
            $container->getTyped($this->advisory, AdvisoryInterface::class),
        );
    }
}
