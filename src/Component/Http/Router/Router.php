<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\UriInterface;
use Neu\Component\Http\Router\Generator\GeneratorInterface;
use Neu\Component\Http\Router\Matcher\MatcherInterface;
use Neu\Component\Http\Router\Matcher\Result;

final readonly class Router implements RouterInterface
{
    private MatcherInterface $matcher;
    private GeneratorInterface $generator;

    public function __construct(MatcherInterface $matcher, GeneratorInterface $generator)
    {
        $this->matcher = $matcher;
        $this->generator = $generator;
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request): Result
    {
        return $this->matcher->match($request);
    }

    /**
     * @inheritDoc
     */
    public function generate(string $name, array $parameters = []): UriInterface
    {
        return $this->generator->generate($name, $parameters);
    }
}
