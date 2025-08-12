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

namespace Neu\Framework\DependencyInjection\Factory\Middleware;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Session\Persistence\PersistenceInterface;
use Neu\Framework\Middleware\SessionMiddleware;

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
    private string $persistence;

    /**
     * @var int
     */
    private int $priority;

    /**
     * @param non-empty-string|null $persistence Session persistence service identifier.
     * @param int|null $priority Middleware priority.
     */
    public function __construct(null|string $persistence = null, null|int $priority = null)
    {
        $this->persistence = $persistence ?? PersistenceInterface::class;
        $this->priority = $priority ?? SessionMiddleware::PRIORITY;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): SessionMiddleware
    {
        return new SessionMiddleware(
            $container->getTyped($this->persistence, PersistenceInterface::class),
            $this->priority,
        );
    }
}
