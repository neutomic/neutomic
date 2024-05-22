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

namespace Neu\Component\Http\Server\Internal;

use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Neu\Component\Http\Message\StatusCode;
use Psr\Log\LoggerInterface;

final readonly class AmphpErrorHandler implements ErrorHandler
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handleError(int $status, null|string $reason = null, null|Request $request = null): Response
    {
        $context = [
            'status' => $status,
            'reason' => $reason ?? StatusCode::tryFrom($status)?->getReasonPhrase(),
        ];

        if ($request !== null) {
            $context['method'] = $request->getMethod();
            $context['uri'] = (string) $request->getUri();
            $context['protocolVersion'] = $request->getProtocolVersion();
            $context['local'] = $request->getClient()->getLocalAddress()->toString();
            $context['remote'] = $request->getClient()->getRemoteAddress()->toString();
        }

        $this->logger->error('An internal server error occurred', $context);

        // Always return a 500 response, as this error always originates from amphp/http-server, not the application.
        return new Response(StatusCode::InternalServerError->value, [
            'content-type' => 'text/plain',
        ], 'Oops! something went wrong. Please try again later.');
    }
}
