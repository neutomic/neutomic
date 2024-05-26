<?php

declare(strict_types=1);

namespace Neu\Examples\Framework;

use Amp\ByteStream\ReadableIterableStream;
use Neu;
use Neu\Component\DependencyInjection\ContainerBuilder;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Router\Route\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

use function Neu\Framework\entrypoint;

require_once __DIR__ . '/../../vendor/autoload.php';

#[Route(name: 'index', path: '/', methods: [Method::Get])]
final readonly class IndexHandler implements HandlerInterface
{
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $stream = new ReadableIterableStream(['Hello', ' ', 'World!']);
        $stream->onClose(function () {
            echo 'Stream closed';
        });

        $context->getClient()->onClose(function () {
            echo 'Client closed';
        });

        $body = Neu\Component\Http\Message\Body::fromReadableStream($stream);

        return Response::fromStatusCode(200)->withBody($body);
    }
}

entrypoint(static function(Project $project): ContainerBuilderInterface {
    $project = $project
        ->withEntryPoint(__FILE__)
        ->withDirectory(__DIR__)
        ->withSource(null)
        ->withConfig(null)
    ;

    $builder = ContainerBuilder::create($project);

    $builder->addConfiguration([
        'http' => [
            'server' => [
                'sockets' => [['host' => '127.0.0.1', 'port' => 1337]]
            ],
            'runtime' => [
                'middleware' => [
                    'x-powered-by' => null,
                    'access-log' => null,
                    'router' => null,
                    'session' => null,
                    'compression' => null,
                    'static-content' => [
                        'roots' => [
                            '/' => __DIR__ . '/public'
                        ]
                    ],
                ]
            ]
        ]
    ]);

    $builder->addExtensions([
        new Neu\Bridge\Monolog\DependencyInjection\MonologExtension(),
        new Neu\Component\Advisory\DependencyInjection\AdvisoryExtension(),
        new Neu\Component\Console\DependencyInjection\ConsoleExtension(),
        new Neu\Component\EventDispatcher\DependencyInjection\EventDispatcherExtension(),
        new Neu\Component\Cache\DependencyInjection\CacheExtension(),
        new Neu\Component\Http\Message\DependencyInjection\MessageExtension(),
        new Neu\Component\Http\Recovery\DependencyInjection\RecoveryExtension(),
        new Neu\Component\Http\Router\DependencyInjection\RouterExtension(),
        new Neu\Component\Http\Runtime\DependencyInjection\RuntimeExtension(),
        new Neu\Component\Http\Server\DependencyInjection\ServerExtension(),
        new Neu\Component\Http\Session\DependencyInjection\SessionExtension(),
    ]);

    return $builder;
});
