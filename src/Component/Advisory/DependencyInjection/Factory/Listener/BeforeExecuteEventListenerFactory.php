<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\DependencyInjection\Factory\Listener;

use Neu\Component\Advisory\AdvisoryInterface;
use Neu\Component\Advisory\Listener\BeforeExecuteEventListener;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see BeforeExecuteEventListener} instance.
 *
 * @implements FactoryInterface<BeforeExecuteEventListener>
 */
final readonly class BeforeExecuteEventListenerFactory implements FactoryInterface
{
    private string $advisory;

    /**
     * @param null|string $advisory The advisory service to use.
     */
    public function __construct(?string $advisory = null)
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
