<?php

declare(strict_types=1);

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
     */
    private string $advisory;

    /**
     * Create a new advisers hook.
     *
     * @param string|null $advisory The advisory service identifier.
     */
    public function __construct(?string $advisory = null)
    {
        $this->advisory = $advisory ?? AdvisoryInterface::class;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(ContainerInterface $container): void
    {
        $advisory = $container->getTyped($this->advisory, AdvisoryInterface::class);

        foreach ($container->getInstancesOf(AdviserInterface::class) as $adviser) {
            $advisory->addAdviser($adviser);
        }
    }
}
