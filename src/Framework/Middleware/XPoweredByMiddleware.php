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
use Neu\Component\Http\Runtime\Middleware\MiddlewareInterface;
use Override;

use const PHP_MAJOR_VERSION;
use const PHP_MINOR_VERSION;

/**
 * Middleware to add the X-Powered-By header to the response.
 *
 * @psalm-suppress ArgumentTypeCoercion
 */
final readonly class XPoweredByMiddleware implements MiddlewareInterface
{
    /**
     * The PHP version string, e.g. "PHP/8.3".
     *
     * @var non-empty-string
     */
    private const string PHP_VERSION = 'PHP/' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

    /**
     * The default powered by header value.
     *
     * @var non-empty-string
     */
    public const string DEFAULT_POWERED_BY = 'Neutomic';

    /**
     * The default value for whether to expose PHP version in the powered by header.
     */
    public const bool DEFAULT_EXPOSE_PHP_VERSION = false;

    /**
     * The powered by header value.
     *
     * @var non-empty-string
     */
    private string $poweredBy;

    /**
     * Whether to expose PHP version in the powered by header.
     */
    private bool $exposePhpVersion;

    /**
     * Create a new instance of the middleware.
     *
     * @param non-empty-string $poweredBy The powered by header value.
     * @param bool $exposePhpVersion Whether to expose PHP version in the powered by header.
     */
    public function __construct(string $poweredBy = self::DEFAULT_POWERED_BY, bool $exposePhpVersion = self::DEFAULT_EXPOSE_PHP_VERSION)
    {
        $this->poweredBy = $poweredBy;
        $this->exposePhpVersion = $exposePhpVersion;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function process(Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        $response = $next->handle($context, $request);

        $values = [$this->poweredBy];
        if ($this->exposePhpVersion) {
            $values[] = self::PHP_VERSION;
        }

        return $response->withHeader('X-Powered-By', $values);
    }
}
