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

namespace Neu\Examples\Console;

use Neu\Component\Console\ApplicationInterface;
use Neu\Component\DependencyInjection\ContainerBuilder;
use Neu\Component\DependencyInjection\Project;
use Neu;
use Psl\Env;
use Psl\SecureRandom;

require_once __DIR__ . '/../../vendor/autoload.php';

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
    /* | Add extensions to the container.       | */
    /* |----------------------------------------| */
    $builder->addExtensions([
        new Neu\Component\EventDispatcher\DependencyInjection\EventDispatcherExtension(),
        new Neu\Component\Console\DependencyInjection\ConsoleExtension(),
    ]);

    /* |----------------------------------------| */
    /* | Add paths to the container builder for | */
    /* | auto-discovery.                        | */
    /* |----------------------------------------| */
    $builder->addPathForAutoDiscovery(__DIR__ . '/Command');

    /* |----------------------------------------| */
    /* | Build the container.                   | */
    /* |----------------------------------------| */
    $container = $builder->build();

    /* |----------------------------------------| */
    /* | Retrieve the application from the      | */
    /* | container.                             | */
    /* |----------------------------------------| */
    $application = $container->getTyped(ApplicationInterface::class, ApplicationInterface::class);

    /* |----------------------------------------| */
    /* | Run the application.                   | */
    /* |----------------------------------------| */
    $application->run();
})();
