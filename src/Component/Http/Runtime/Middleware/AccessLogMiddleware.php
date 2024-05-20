<?php

declare(strict_types=1);

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
        $duration = microtime(true) - $time;

        $message = Str\format(
            '"%s %s" %d HTTP/%s @ %s | %s ms',
            $request->getMethod()->value,
            $request->getUri()->toString(),
            $response->getStatusCode(),
            $request->getProtocolVersion()->value,
            $context->getRemoteAddress(),
            number_format($duration * 1000, 2),
        );

        $this->logger->info($message, [
            'remote' => $context->getRemoteAddress(),
            'local' => $context->getLocalAddress(),
            'client' => $context->getClientId(),
            'worker' => $context->getWorkerId(),
        ]);

        return $response;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
