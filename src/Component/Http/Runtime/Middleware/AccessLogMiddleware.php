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
use Psl\Str;
use Psr\Log\LoggerInterface;

use function microtime;
use function number_format;

final readonly class AccessLogMiddleware implements PrioritizedMiddlewareInterface
{
    public const int PRIORITY = -512;

    private int $priority;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, int $priority = self::PRIORITY)
    {
        $this->logger = $logger;
        $this->priority = $priority;
    }

    public function process(Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        $time = microtime(true);
        $response = $next->handle($context, $request);
        $duration = (int) (microtime(true) - $time);

        $message = Str\format(
            '"%s %s" %d HTTP/%s @ %s | %s ms',
            $request->getMethod()->value,
            $request->getUri()->toString(),
            $response->getStatusCode(),
            $request->getProtocolVersion()->value,
            $context->getClient()->getRemoteAddress()->toString(),
            number_format($duration * 1000, 2),
        );

        $this->logger->info($message, [
            'remote' => $context->getClient()->getRemoteAddress()->toString(),
            'local' => $context->getClient()->getLocalAddress()->toString(),
            'client' => $context->getClient()->getId(),
            'worker' => $context->getWorkerId(),
        ]);

        return $response;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
