<?php

declare(strict_types=1);

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
    private string $matcher;
    private string $generator;

    public function __construct(?string $matcher = null, ?string $generator = null)
    {
        $this->matcher = $matcher ?? MatcherInterface::class;
        $this->generator = $generator ?? GeneratorInterface::class;
    }

    public function __invoke(ContainerInterface $container): object
    {
        return new Router(
            $container->getTyped($this->matcher, MatcherInterface::class),
            $container->getTyped($this->generator, GeneratorInterface::class),
        );
    }
}
