<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\DependencyInjection\Hook;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\HookInterface;
use Neu\Component\Http\Runtime\Middleware\MiddlewareInterface;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueueInterface;
use Psl\Iter;

/**
 * A hook that enqueues all middleware instances into the middleware queue.
 */
final readonly class EnqueueMiddlewareHook implements HookInterface
{
    private string $queue;

    /**
     * @var list<non-empty-string>
     */
    private array $ignore;

    /**
     * @param non-empty-string|null $queue
     * @param list<non-empty-string> $ignore
     */
    public function __construct(?string $queue = null, array $ignore = [])
    {
        $this->queue = $queue ?? MiddlewareQueueInterface::class;
        $this->ignore = $ignore;
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(ContainerInterface $container): void
    {
        $middlewareStack = $container->getTyped($this->queue, MiddlewareQueueInterface::class);

        foreach ($container->getInstancesOf(MiddlewareInterface::class) as $middleware) {
            if (Iter\contains($this->ignore, $middleware::class)) {
                continue;
            }

            $middlewareStack->enqueue($middleware);
        }
    }
}
