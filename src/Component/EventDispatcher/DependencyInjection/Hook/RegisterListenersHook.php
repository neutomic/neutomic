<?php

declare(strict_types=1);

namespace Neu\Component\EventDispatcher\DependencyInjection\Hook;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\HookInterface;
use Neu\Component\EventDispatcher\Attribute\Listener;
use Neu\Component\EventDispatcher\Listener\ListenerInterface;
use Neu\Component\EventDispatcher\Listener\Registry\RegistryInterface;
use Psl\Iter;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use ReflectionObject;

/**
 * Hook for registering event listeners.
 */
final readonly class RegisterListenersHook implements HookInterface
{
    private string $registry;
    private string $logger;

    /**
     * Creates a new registry hook.
     *
     * @param string|null $registry The registry service identifier, defaults to {@see RegistryInterface::class}.
     * @param string|null $logger The logger service identifier, defaults to {@see LoggerInterface::class}.
     */
    public function __construct(?string $registry = null, ?string $logger = null)
    {
        $this->registry = $registry ?? RegistryInterface::class;
        $this->logger = $logger ?? LoggerInterface::class;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(ContainerInterface $container): void
    {
        $registry = $container->getTyped($this->registry, RegistryInterface::class);
        $logger = null;
        if ($container->has($this->logger)) {
            $logger = $container->getTyped($this->logger, LoggerInterface::class);
        }

        $registered = [];
        foreach ($container->getAttributed(Listener::class) as $listener) {
            if (!$listener instanceof ListenerInterface) {
                throw new RuntimeException('The event listener "' . $listener::class . '" must implement "' . ListenerInterface::class . '".');
            }

            foreach ($this->readAttribute($listener) as $attribute) {
                foreach ($attribute->events as $event) {
                    $registry->register($event, $listener, $attribute->priority);

                    $logger?->info(
                        'Registered event listener "' . $listener::class . '" for event "' . $event . '" with priority ' . $attribute->priority . '.',
                    );
                }
            }

            $registered[] = $listener::class;
        }

        if (null !== $logger) {
            foreach ($container->getInstancesOf(ListenerInterface::class) as $listener) {
                if (!Iter\contains($registered, $listener::class)) {
                    $logger->warning(
                        'The event listener "' . $listener::class . '" was not registered, as it does not use the "' . Listener::class . '" attribute.',
                    );
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
