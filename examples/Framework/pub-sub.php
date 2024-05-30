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
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
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
use Revolt\EventLoop;

use function Neu\Framework\entrypoint;

require_once __DIR__ . '/../../vendor/autoload.php';

/** @psalm-suppress MissingThrowsDocblock */
#[Route(name: 'pub', pattern: '/pub', methods: [Method::Post])]
final readonly class PubHandler implements HandlerInterface
{
    public function __construct(private ParserInterface $parser, private HubInterface $hub)
    {
    }

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

entrypoint(static function (Project $project): ContainerBuilderInterface {
    $project = $project->withConfig(null);

    $builder = ContainerBuilder::create($project);
    $builder->addConfiguration([
        'http' => [
            'server' => [
                'sockets' => [['host' => '127.0.0.1', 'port' => 1337]]
            ],
        ],
    ]);

    $builder->addExtensions([
        new Neu\Bridge\Monolog\DependencyInjection\MonologExtension(),
        new Neu\Framework\DependencyInjection\FrameworkExtension(),
        new Neu\Component\Broadcast\DependencyInjection\BroadcastExtension(),
    ]);

    return $builder;
});
