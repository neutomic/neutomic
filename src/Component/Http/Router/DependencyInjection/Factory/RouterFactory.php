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

namespace Neu\Component\Http\Router\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Router\Generator\GeneratorInterface;
use Neu\Component\Http\Router\Matcher\MatcherInterface;
use Neu\Component\Http\Router\Router;

/**
 * A factory for creating instances of the Router.
 *
 * @implements FactoryInterface<Router>
 */
final readonly class RouterFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $matcher;

    /**
     * @var non-empty-string
     */
    private string $generator;

    /**
     * Create a new {@see RouterFactory} instance.
     *
     * @param non-empty-string|null $matcher The matcher service identifier.
     * @param non-empty-string|null $generator The generator service identifier.
     */
    public function __construct(null|string $matcher = null, null|string $generator = null)
    {
        $this->matcher = $matcher ?? MatcherInterface::class;
        $this->generator = $generator ?? GeneratorInterface::class;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container): object
    {
        return new Router(
            $container->getTyped($this->matcher, MatcherInterface::class),
            $container->getTyped($this->generator, GeneratorInterface::class),
        );
    }
}
