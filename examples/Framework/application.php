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

namespace Neu\Examples\Framework;

use Neu;
use Neu\Component\DependencyInjection\ContainerBuilder;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\ServerSentEvent;
use Psl\Async;

use Psl\DateTime\Duration;

use function Neu\Framework\entrypoint;

require_once __DIR__ . '/../../vendor/autoload.php';

#[Route(name: 'index', pattern: '/', methods: [Method::Get])]
final readonly class IndexHandler implements HandlerInterface
{
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        return Response\redirect('/index.html', StatusCode::TemporaryRedirect);
    }
}

#[Route(name: 'server-sent-events', pattern: '/sse', methods: [Method::Get])]
final readonly class ServerSentEventsHandler implements HandlerInterface
{
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $stream = ServerSentEvent\EventStream::forContext($context);

        Async\run(static function () use ($stream): void {
            while (true) {
                if ($stream->isClosed()) {
                    break;
                }

                $stream->send(new ServerSentEvent\Event(
                    type: 'message',
                    data: 'Hello, World!',
                ));

                Async\sleep(Duration::seconds(1));
            }
        })->ignore();

        return $stream->getResponse();
    }
}

entrypoint(static function (Project $project): ContainerBuilderInterface {
    $project = $project->withConfig(null);

    $builder = ContainerBuilder::create($project);

    $builder->addConfiguration([
        'framework' => [
            'middleware' => [
                'static-content' => [
                    'roots' => [
                        '/' => __DIR__ . '/public'
                    ]
                ],
            ]
        ],
        'monolog' => [
            'default' => 'main',
            'channels' => [
                'main' => [
                    'handlers' => [
                        'monolog.handler.stderr',
                    ],
                ]
            ],
            'handlers' => [
                'stderr' => [
                    'type' => 'stderr',
                    'level' => $project->mode->isProduction() ? 'notice' : 'debug',
                    'formatter' => $project->mode->isProduction() ? 'monolog.formatter.line' : 'monolog.formatter.console',
                ],
            ],
        ],
        'http' => [
            'server' => [
                'sockets' => [['host' => '127.0.0.1', 'port' => 1337]]
            ],
        ]
    ]);

    $builder->addExtensions([
        new Neu\Bridge\Monolog\DependencyInjection\MonologExtension(),
        new Neu\Framework\DependencyInjection\FrameworkExtension(),
    ]);

    return $builder;
});
