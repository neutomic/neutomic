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

use Amp\Cluster\Cluster;
use Neu;
use Neu\Component\DependencyInjection\ContainerBuilder;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Router\RouteCollector;

use Neu\Component\Http\Server\ClusterWorkerInterface;
use Revolt\EventLoop;
use function Neu\Framework\entrypoint;

require_once __DIR__ . '/../../vendor/autoload.php';

entrypoint(static function (Project $project): ContainerInterface {
    $project = $project->withConfig(null);

    $builder = ContainerBuilder::create($project, [
        'framework' => [
            'middleware' => [
                'access-log' => false,
                'session' => false,
                'compression' => false,
                'static-content' => false,
            ],
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
                'sockets' => [['host' => '127.0.0.1', 'port' => 2020]]
            ],
        ]
    ]);

    $builder->addExtensions([
        new Neu\Framework\DependencyInjection\FrameworkExtension(),
        new Neu\Bridge\Monolog\DependencyInjection\MonologExtension(),
    ]);

    $container = $builder->build();

    $worker = $container->getTyped(ClusterWorkerInterface::class, ClusterWorkerInterface::class);
    if ($worker->isInWorkerContext()) {
        EventLoop::unreference(EventLoop::repeat(1, static function () use($worker) {
            try {
                $parcel = $worker->getParcel();
            } catch (Neu\Component\Http\Exception\RuntimeException $e) {
                // The worker is not initialized yet.
                return;
            }

            $id = $worker->getWorkerId();
            $parcel->synchronized(static function (?array $values) use($id): array {
                if ($values === null) {
                    $values = [];
                }

                $usage = \memory_get_usage();
                $peak = \memory_get_peak_usage();

                $values[$id] = [
                    'memory' => [
                        'usage' => $usage,
                        'usage.human' => \number_format($usage / 1024 / 1024, 2) . ' MB',
                        'peak' => $peak,
                        'peak.human' => \number_format($peak / 1024 / 1024, 2) . ' MB',
                    ],
                ];

                return $values;
            });
        }));
    }

    /** @var RouteCollector $collector */
    $collector = $container->get(RouteCollector::class);
    $collector->get('index', '/', static function() {
        return Response\json([
            'hello' => 'world',
        ]);
    });

    $collector->get('mem', '/mem', static function () use($worker) {
        $parcel = $worker->getParcel();
        $id = $worker->getWorkerId();
        $values = $parcel->unwrap();

        return Response\json([
            'worker' => $id,
            'values' => $values,
        ]);
    });

    return $container;
});
