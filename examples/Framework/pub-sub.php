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
use Neu\Component\Broadcast\HubInterface;
use Neu\Component\DependencyInjection\ContainerBuilder;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Message\Form\ParseOptions;
use Neu\Component\Http\Message\Form\ParserInterface;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\ServerSentEvent;
use Neu\Framework\EngineInterface;
use Revolt\EventLoop;
use Psl\Env;
use Psl\SecureRandom;

require_once __DIR__ . '/../../vendor/autoload.php';

/** @psalm-suppress MissingThrowsDocblock */
#[Route(name: 'pub', pattern: '/pub', methods: [Method::Post])]
final readonly class PubHandler implements HandlerInterface
{
    public function __construct(private ParserInterface $parser, private HubInterface $hub)
    {
    }

    #[\Override]
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $form = $this->parser->parse($request, ParseOptions::fromFieldCountLimit(4)->withFileCountLimit(0));

        $topic = $form->getFirstFieldByName('topic')?->getBody()?->getContents();
        $message = $form->getFirstFieldByName('message')?->getBody()?->getContents();
        if (null === $topic || '' === $topic || null === $message) {
            return Response\text('Invalid form data!');
        }

        /** @var Neu\Component\Broadcast\Channel<string> $channel */
        $channel = $this->hub->getChannel($topic);
        $channel->broadcast($message);

        return Response\text('Message published!');
    }
}

/** @psalm-suppress MissingThrowsDocblock */
#[Route(name: 'sub', pattern: '/sub', methods: [Method::Get])]
final readonly class SubHandler implements HandlerInterface
{
    public function __construct(private HubInterface $hub)
    {
    }

    #[\Override]
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $topics = $request->getQueryParameter('topics');
        if (null === $topics) {
            return Response\text('No topics provided!');
        }

        $stream = ServerSentEvent\EventStream::forContext($context);

        foreach ($topics as $topic) {
            if ('' === $topic) {
                continue;
            }

            /** @var Neu\Component\Broadcast\Subscription<string> $subscription */
            $subscription = $this->hub->getChannel($topic)->subscribe();

            EventLoop::queue(static function () use ($subscription, $stream) {
                while ($message = $subscription->receive()) {
                    if ($stream->isClosed()) {
                        $subscription->cancel();

                        return;
                    }

                    $stream->send(new ServerSentEvent\Event($message->getPayload(), $subscription->getChannel()));
                }

                $stream->close();
            });
        }

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
                'static-content' => false,
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
        new Neu\Component\Broadcast\DependencyInjection\BroadcastExtension(),
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
