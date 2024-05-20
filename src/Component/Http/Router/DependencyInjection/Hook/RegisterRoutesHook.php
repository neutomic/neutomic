<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\DependencyInjection\Hook;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\HookInterface;
use Neu\Component\Http\Router\Route\Registry\RegistryInterface;
use Neu\Component\Http\Router\Route\Route;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use ReflectionAttribute;
use ReflectionObject;

/**
 * Hook for registering routes in the registry.
 */
final readonly class RegisterRoutesHook implements HookInterface
{
    /**
     * The registry service identifier.
     */
    private string $registry;

    /**
     * Create a new {@see RegisterRoutesHook} instance.
     *
     * @param string|null $registry The registry service identifier.
     * @param string|null $logger The logger service identifier.
     */
    public function __construct(?string $registry = null)
    {
        $this->registry = $registry ?? RegistryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): void
    {
        $registry = $container->getTyped($this->registry, RegistryInterface::class);

        foreach ($container->getAttributed(Route::class) as $handler) {
            if (!$handler instanceof HandlerInterface) {
                throw new RuntimeException('The route handler "' . $handler::class . '" must implement "' . HandlerInterface::class . '".');
            }

            foreach ($this->readRoute($handler) as $route) {
                $registry->register($route, $handler);
            }
        }
    }

    /**
     * @return iterable<Route>
     */
    private function readRoute(HandlerInterface $handler): iterable
    {
        $reflection = new ReflectionObject($handler);
        $attributes = $reflection->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);
        $configurations = [];
        foreach ($attributes as $attribute) {
            $configurations[] = $attribute->newInstance();
        }

        return $configurations;
    }
}
