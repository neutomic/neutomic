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

namespace Neu\Component\EventDispatcher\DependencyInjection\Hook;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\HookInterface;
use Neu\Component\EventDispatcher\Attribute\Listener;
use Neu\Component\EventDispatcher\Listener\ListenerInterface;
use Neu\Component\EventDispatcher\Listener\Registry\RegistryInterface;
use ReflectionAttribute;
use ReflectionObject;
use Override;

/**
 * Hook for registering event listeners.
 */
final readonly class RegisterListenersHook implements HookInterface
{
    /**
     * @var non-empty-string
     */
    private string $registry;

    /**
     * Creates a new registry hook.
     *
     * @param non-empty-string|null $registry The registry service identifier, defaults to {@see RegistryInterface::class}.
     */
    public function __construct(null|string $registry = null)
    {
        $this->registry = $registry ?? RegistryInterface::class;
    }

    /**
     * @throws ExceptionInterface
     */
    #[Override]
    public function __invoke(ContainerInterface $container): void
    {
        $registry = $container->getTyped($this->registry, RegistryInterface::class);

        foreach ($container->getAttributed(Listener::class) as $listener) {
            if (!$listener instanceof ListenerInterface) {
                throw new RuntimeException('The event listener "' . $listener::class . '" must implement "' . ListenerInterface::class . '".');
            }

            foreach ($this->readAttribute($listener) as $attribute) {
                foreach ($attribute->events as $event) {
                    $registry->register($event, $listener, $attribute->priority);
                }
            }
        }
    }

    /**
     * @return iterable<Listener>
     */
    private function readAttribute(ListenerInterface $listener): iterable
    {
        $reflection = new ReflectionObject($listener);
        $attributes = $reflection->getAttributes(Listener::class, ReflectionAttribute::IS_INSTANCEOF);
        $configurations = [];
        foreach ($attributes as $attribute) {
            $configurations[] = $attribute->newInstance();
        }

        return $configurations;
    }
}
