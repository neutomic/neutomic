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
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Router\RouteCollector;

use function Neu\Framework\entrypoint;

require_once __DIR__ . '/../../vendor/autoload.php';

entrypoint(static function (Project $project): ContainerInterface {
    $project = $project->withConfig(null);

    $builder = ContainerBuilder::create($project, [
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
        new Neu\Framework\DependencyInjection\FrameworkExtension(),
        new Neu\Bridge\Monolog\DependencyInjection\MonologExtension(),
    ]);

    $container = $builder->build();

    /** @var RouteCollector $collector */
    $collector = $container->get(RouteCollector::class);
    $collector->get('index', '/', static function () {
        return Response\text('Hello, World!');
    });

    return $container;
});
