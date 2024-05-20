<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Runtime\Middleware\SessionMiddleware;
use Neu\Component\Http\Session\Initializer\InitializerInterface;
use Neu\Component\Http\Session\Persistence\PersistenceInterface;

/**
 * Factory for creating a {@see SessionMiddleware} instance.
 *
 * @implements FactoryInterface<SessionMiddleware>
 */
final readonly class SessionMiddlewareFactory implements FactoryInterface
{
    private string $initializer;
    private string $persistence;
    private int $priority;

    public function __construct(?string $initializer = null, ?string $persistence = null, ?int $priority = null)
    {
        $this->initializer = $initializer ?? InitializerInterface::class;
        $this->persistence = $persistence ?? PersistenceInterface::class;
        $this->priority = $priority ?? SessionMiddleware::PRIORITY;
    }

    public function __invoke(ContainerInterface $container): SessionMiddleware
    {
        return new SessionMiddleware(
            $container->getTyped($this->initializer, InitializerInterface::class),
            $container->getTyped($this->persistence, PersistenceInterface::class),
            $this->priority,
        );
    }
}
