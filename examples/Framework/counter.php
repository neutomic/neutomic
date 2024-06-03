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
use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\Session\Configuration\CacheLimiter;
use Neu\Framework\EngineInterface;
use Psl\SecureRandom;
use Psl\Env;

require_once __DIR__ . '/../../vendor/autoload.php';

#[Route(name: 'index', pattern: '/', methods: [Method::Get])]
final readonly class IndexHandler implements HandlerInterface
{
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $session = $request->getSession();
        $counter = (string) $session->update('counter', static function (null|int $value): int {
            return null === $value ? 1 : $value + 1;
        });

        return Response\text('Hello, World! You have visited this page ' . $counter . ' times.');
    }
}

(static function (): void {
    // Retrieve the project secret.
    $secret = Env\get_var('PROJECT_SECRET');
    if (null === $secret) {
        // Generate a new secret.
        $secret = SecureRandom\string(32);
        // Store the secret in the environment.
        Env\set_var('PROJECT_SECRET', $secret);
    }

    // Create a project instance.
    $project = Project::create(secret: $secret, directory: __DIR__, entrypoint: __FILE__);

    // Create a container builder.
    $builder = ContainerBuilder::create($project);

    // Add configuration to the container builder.
    $builder->addConfiguration([
        'framework' => [
            'middleware' => [
                'compression' => false,
                'static-content' => false,
            ]
        ],
        'http' => [
            'server' => [
                'sockets' => [[
                    'host' => '127.0.0.1',
                    'port' => 1337,
                ]]
            ],
            'session' => [
                'cache' => [
                    'limiter' => CacheLimiter::Public,
                ]
            ]
        ]
    ]);

    // Add extensions to the container.
    $builder->addExtensions([
        new Neu\Bridge\Monolog\DependencyInjection\MonologExtension(),
        new Neu\Framework\DependencyInjection\FrameworkExtension(),
    ]);

    // Build the container.
    $container = $builder->build();

    // Retrieve the engine from the container.
    $engine = $container->getTyped(EngineInterface::class, EngineInterface::class);

    // Run the engine.
    $engine->run();
})();
