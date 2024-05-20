<?php

declare(strict_types=1);

namespace Neu\Examples\Console;

use Neu\Component\Console\DependencyInjection\ConsoleExtension;
use Neu\Component\DependencyInjection\ContainerBuilder;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\EventDispatcher\DependencyInjection\EventDispatcherExtension;

use function Neu\Framework\entrypoint;

require_once __DIR__ . '/../../vendor/autoload.php';

entrypoint(static function (Project $project): ContainerBuilderInterface {
    $project = $project->withSource(__DIR__ . '/Command');

    $container = ContainerBuilder::create($project);

    $container->addExtensions([
        new EventDispatcherExtension(),
        new ConsoleExtension(),
    ]);

    return $container;
});
