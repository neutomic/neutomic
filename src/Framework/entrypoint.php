<?php

declare(strict_types=1);

namespace Neu\Framework;

use Amp\Cluster\Cluster;
use Closure;
use Neu\Component\Console\ApplicationInterface;
use Neu\Component\Console\Terminal;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Server\ClusterWorkerInterface;
use Psl\Env;

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
 * @param (Closure(Project): ContainerBuilderInterface) $closure A closure that takes a {@see Project} instance
 *                                                               and returns a {@see ContainerBuilderInterface}.
 *
 * @note This function assumes that the container builder returned from the closure contains a definition
 *  for console application ({@see ApplicationInterface}) and the HTTP server cluster worker ({@see ClusterWorkerInterface}).
 *
 * @throws ExceptionInterface If there is an error during the container building or project initialization.
 */
function entrypoint(Closure $closure): void
{
    Internal\Internal::fixAmpFilesystemForWorker();

    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $entrypoint = $backtrace[0]['file'] ?? null;
    if (!is_string($entrypoint)) {
        throw new Exception\RuntimeException('Failed to determine the entry point file.');
    }

    $builder = $closure(
        Project::create(dirname($entrypoint, 2), $entrypoint),
    );

    $container = $builder->build();

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
        $application = $container->getTyped(ApplicationInterface::class, ApplicationInterface::class);
        $application->run();
    }
}