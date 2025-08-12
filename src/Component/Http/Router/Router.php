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

namespace Neu\Component\Http\Router;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\UriInterface;
use Neu\Component\Http\Router\Generator\GeneratorInterface;
use Neu\Component\Http\Router\Matcher\MatcherInterface;
use Neu\Component\Http\Router\Matcher\Result;
use Override;

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
    #[Override]
    public function match(RequestInterface $request): Result
    {
        return $this->matcher->match($request);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function generate(string $name, array $parameters = []): UriInterface
    {
        return $this->generator->generate($name, $parameters);
    }
}
