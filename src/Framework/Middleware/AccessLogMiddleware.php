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
use Psl\Str;
use Psl\DateTime;
use Psr\Log\LoggerInterface;

/**
 * Middleware that logs access to the server.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class AccessLogMiddleware implements PrioritizedMiddlewareInterface
{
    public const int PRIORITY = -512;

    /**
     * The logger to use for logging.
     */
    private LoggerInterface $logger;

    /**
     * The priority of the middleware.
     */
    private int $priority;

    /**
     * Create a new {@see AccessLogMiddleware} instance.
     *
     * @param LoggerInterface $logger The logger to use for logging.
     * @param int $priority The priority of the middleware.
     */
    public function __construct(LoggerInterface $logger, int $priority = self::PRIORITY)
    {
        $this->logger = $logger;
        $this->priority = $priority;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function process(Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        $time = DateTime\Timestamp::monotonic();
        $response = $next->handle($context, $request);
        $duration = DateTime\Timestamp::monotonic()->since($time)->getTotalMilliseconds();

        $message = Str\format(
            '"%s %s" %d HTTP/%s @ %s | %s ms',
            $request->getMethod()->value,
            $request->getUri()->toString(),
            $response->getStatusCode(),
            $request->getProtocolVersion()->value,
            $context->getClient()->getRemoteAddress()->toString(),
            Str\format_number($duration, 2),
        );

        $this->logger->info($message, [
            'remote' => $context->getClient()->getRemoteAddress()->toString(),
            'local' => $context->getClient()->getLocalAddress()->toString(),
            'client' => $context->getClient()->getId(),
            'worker' => $context->getWorkerId(),
        ]);

        return $response;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getPriority(): int
    {
        return $this->priority;
    }
}
