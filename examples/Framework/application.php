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
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Framework\EngineInterface;
use Psl\DateTime\Duration;
use Psl\SecureRandom;
use Psl\Env;
use Neu\Component\Http\ServerSentEvent;
use Psl\Async;

require_once __DIR__ . '/../../vendor/autoload.php';

#[Route(name: 'redirect', pattern: '/', methods: [Method::Get])]
final readonly class RedirectHandler implements HandlerInterface
{
    #[\Override]
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        return Response\redirect('/index.html', StatusCode::TemporaryRedirect);
    }
}

#[Route(name: 'server-sent-events', pattern: '/sse', methods: [Method::Get])]
final readonly class ServerSentEventsHandler implements HandlerInterface
{
    #[\Override]
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

/* |----------------------------------------| */
/* | Run the engine inside a closure        | */
/* | to avoid leaking variables to the      | */
/* | global scope.                          | */
/* |----------------------------------------| */
(static function (): void {
    /* |----------------------------------------| */
    /* | Retrieve the project secret.           | */
    /* |----------------------------------------| */
    $secret = Env\get_var('PROJECT_SECRET');
    $generated = false;

    /* |----------------------------------------| */
    /* | Check if the secret is missing.        | */
    /* |----------------------------------------| */
    if (null === $secret) {
        /* |----------------------------------------| */
        /* | Generate a new secret.                 | */
        /* |----------------------------------------| */
        $secret = SecureRandom\string(32);

        /* |----------------------------------------| */
        /* | Store the secret in the environment.   | */
        /* |----------------------------------------| */
        Env\set_var('PROJECT_SECRET', $secret);

        /* |----------------------------------------| */
        /* | Mark the secret as generated.          | */
        /* |----------------------------------------| */
        $generated = true;
    }

    /* |----------------------------------------| */
    /* | Create a project instance.             | */
    /* |----------------------------------------| */
    $project = Project::create(secret: $secret, directory: __DIR__, entrypoint: __FILE__);

    /* |----------------------------------------| */
    /* | Check if the project is in production. | */
    /* |----------------------------------------| */
    if ($project->mode->isProduction() && $generated) {
        /* |----------------------------------------| */
        /* | Throw an exception if the project      | */
        /* | secret has been generated in           | */
        /* | production mode.                       | */
        /* |----------------------------------------| */
        throw new Neu\Framework\Exception\RuntimeException('The project secret has been generated in production mode.');
    }

    /* |----------------------------------------| */
    /* | Create a container builder.            | */
    /* |----------------------------------------| */
    $builder = ContainerBuilder::create($project);

    /* |----------------------------------------| */
    /* | Add configuration to the container.    | */
    /* |----------------------------------------| */
    $builder->addConfigurationResource([
        'framework' => [
            'middleware' => [
                'static-content' => [
                    'roots' => [
                        '/' => __DIR__ . '/public'
                    ]
                ],
                'session' => false,
                'compression' => false,
            ]
        ],
        'http' => [
            'server' => [
                'sockets' => [[
                    'host' => '127.0.0.1',
                    'port' => 1337,
                ]]
            ],
        ]
    ]);

    /* |----------------------------------------| */
    /* | Add extensions to the container.       | */
    /* |----------------------------------------| */
    $builder->addExtensions([
        new Neu\Bridge\Monolog\DependencyInjection\MonologExtension(),
        new Neu\Framework\DependencyInjection\FrameworkExtension(),
    ]);

    /* |----------------------------------------| */
    /* | Add paths to the container builder for | */
    /* | auto-discovery.                        | */
    /* |----------------------------------------| */
    $builder->addPathForAutoDiscovery(__FILE__);

    /* |----------------------------------------| */
    /* | Build the container.                   | */
    /* |----------------------------------------| */
    $container = $builder->build();

    /* |----------------------------------------| */
    /* | Retrieve the engine from the           | */
    /* | container.                             | */
    /* |----------------------------------------| */
    $engine = $container->getTyped(EngineInterface::class, EngineInterface::class);

    /* |----------------------------------------| */
    /* | Run the engine.                        | */
    /* |----------------------------------------| */
    $engine->run();
})();
