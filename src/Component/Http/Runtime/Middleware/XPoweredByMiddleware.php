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

namespace Neu\Component\Http\Runtime\Middleware;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

use const PHP_VERSION;

final readonly class XPoweredByMiddleware implements MiddlewareInterface
{
    /**
     * The default powered by header value.
     *
     * @var non-empty-string
     */
    public const string DEFAULT_POWERED_BY = 'Neutomic Framework / PHP ' . PHP_VERSION;

    /**
     * The powered by header value.
     *
     * @var non-empty-string
     */
    private string $poweredBy;

    /**
     * Create a new instance of the middleware.
     *
     * @param non-empty-string $poweredBy The powered by header value.
     */
    public function __construct(string $poweredBy = self::DEFAULT_POWERED_BY)
    {
        $this->poweredBy = $poweredBy;
    }

    /**
     * @inheritDoc
     */
    public function process(Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        $response = $next->handle($context, $request);

        return $response->withHeader('X-Powered-By', $this->poweredBy);
    }
}
