<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router;

use Neu\Component\Http\Router\Generator\GeneratorInterface;
use Neu\Component\Http\Router\Matcher\MatcherInterface;

interface RouterInterface extends GeneratorInterface, MatcherInterface
{
}
