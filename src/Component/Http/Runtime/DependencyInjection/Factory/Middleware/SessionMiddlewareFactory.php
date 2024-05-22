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
    /**
     * @var non-empty-string
     */
    private string $initializer;

    /**
     * @var non-empty-string
     */
    private string $persistence;

    /**
     * @var int
     */
    private int $priority;

    /**
     * @param non-empty-string|null $initializer Session initializer service identifier.
     * @param non-empty-string|null $persistence Session persistence service identifier.
     * @param int|null $priority Middleware priority.
     */
    public function __construct(null|string $initializer = null, null|string $persistence = null, null|int $priority = null)
    {
        $this->initializer = $initializer ?? InitializerInterface::class;
        $this->persistence = $persistence ?? PersistenceInterface::class;
        $this->priority = $priority ?? SessionMiddleware::PRIORITY;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): SessionMiddleware
    {
        return new SessionMiddleware(
            $container->getTyped($this->initializer, InitializerInterface::class),
            $container->getTyped($this->persistence, PersistenceInterface::class),
            $this->priority,
        );
    }
}
