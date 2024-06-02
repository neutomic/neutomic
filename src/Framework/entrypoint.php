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

namespace Neu\Framework;

use Amp\Cluster\Cluster;
use Closure;
use Neu\Component\Console\ApplicationInterface;
use Neu\Component\Console\Terminal;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Server\ClusterWorkerInterface;
use Psl\Env;
use Throwable;

use function debug_backtrace;
use function dirname;
use function is_string;

use const DEBUG_BACKTRACE_IGNORE_ARGS;

/**
 * Entry point function for every Neu project.
 *
 * This function bootstraps the project by creating a project instance,
 * building the dependency injection container, and running the application
 * or HTTP server worker based on the context.
 *
 * @psalm-type Container = ContainerBuilderInterface|ContainerInterface
 * @psalm-type Bootstrap = (Closure(Project): Container)
 *
 * @param Bootstrap $closure A closure that takes a {@see Project} instance and returns a {@see Container}.
 *
 * @note This function assumes that the container builder returned from the closure contains a definition
 *  for console application ({@see ApplicationInterface}) and the HTTP server cluster worker ({@see ClusterWorkerInterface}).
 *
 * @psalm-suppress MissingThrowsDocblock
 */
function entrypoint(Closure $closure): void
{
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $entrypoint = $backtrace[0]['file'] ?? null;
    if (!is_string($entrypoint) || '' === $entrypoint) {
        throw new Exception\RuntimeException('Failed to determine the entry point file.');
    }

    $container = $closure(
        Project::create(dirname($entrypoint, 2), $entrypoint),
    );

    if ($container instanceof ContainerBuilderInterface) {
        $container = $container->build();
    }

    if (!$container->has(ClusterWorkerInterface::class) || !$container->has(ApplicationInterface::class)) {
        throw new Exception\RuntimeException(
            'The container must contain a definition for the application and the cluster worker, did you forget to register the console or HTTP server extensions?',
        );
    }

    $project = $container->getProject();
    if ($project->debug) {
        Env\set_var('AMP_DEBUG', '1');
        Env\set_var('REVOLT_DRIVER_DEBUG_TRACE', '1');
    }

    try {
        if (Cluster::isWorker()) {
            Env\set_var('NONINTERACTIVE', '1');

            $worker = $container->getTyped(ClusterWorkerInterface::class, ClusterWorkerInterface::class);
            $worker->start();
            Cluster::awaitTermination();
            $worker->stop();
        } else {
            Env\set_var('COLUMNS', (string) Terminal::getWidth());
            Env\set_var('LINES', (string) Terminal::getHeight());
            Env\set_var('CLICOLORS', Terminal::hasColorSupport() ? '1' : '0');
            Env\set_var('AMP_LOG_COLOR', Terminal::hasColorSupport() ? '1' : '0');

            // we are in the main process, run the application.
            /**
             * @var ApplicationInterface $application
             */
            $application = $container->getTyped(ApplicationInterface::class, ApplicationInterface::class);
            $application->run();
        }
    } catch (Throwable $e) {
        throw new Exception\RuntimeException('Failed to bootstrap the project.', 0, $e);
    } finally {
        $container->dispose();
    }
}
