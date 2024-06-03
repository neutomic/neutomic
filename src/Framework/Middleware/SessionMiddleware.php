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

namespace Neu\Framework\Middleware;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\Runtime\Middleware\PrioritizedMiddlewareInterface;
use Neu\Component\Http\Session\Persistence\PersistenceInterface;

final readonly class SessionMiddleware implements PrioritizedMiddlewareInterface
{
    public const int PRIORITY = -64;

    private PersistenceInterface $persistence;
    private int $priority;

    public function __construct(PersistenceInterface $persistence, int $priority = self::PRIORITY)
    {
        $this->persistence = $persistence;
        $this->priority = $priority;
    }

    public function process(Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        $request = $this->persistence->initialize($context, $request);

        $response = $next->handle($context, $request);

        return $this->persistence->persist($context, $request, $response);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
