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

namespace Neu\Framework\DependencyInjection\Factory\Command\Http\Server;

use Assess\Configuration;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Server\ClusterInterface;
use Neu\Framework\Command\Http\Server\ClusterCommand;
use Override;

/**
 * @implements FactoryInterface<ClusterCommand>
 */
final readonly class ClusterCommandFactory implements FactoryInterface
{
    /**
     * The interval in seconds to poll for changes.
     *
     * @var float
     */
    private float $watchInterval;

    /**
     * The directories to watch for changes.
     *
     * @var list<non-empty-string>
     */
    private array $watchDirectories;

    /**
     * The extensions to watch for changes.
     *
     * @var list<non-empty-string>
     */
    private array $watchExtensions;

    /**
     * @param float|null $watchInterval The interval in seconds to poll for changes.
     * @param list<non-empty-string>|null $watchDirectories The directories to watch for changes.
     * @param list<non-empty-string>|null $watchExtensions The extensions to watch for changes.
     */
    public function __construct(null|float $watchInterval = null, null|array $watchDirectories = null, null|array $watchExtensions = null)
    {
        $this->watchInterval = $watchInterval ?? Configuration::DEFAULT_POLL_INTERVAL;
        $this->watchDirectories = $watchDirectories ?? [];
        $this->watchExtensions = $watchExtensions ?? [];
    }

    #[Override]
    public function __invoke(ContainerInterface $container): ClusterCommand
    {
        $project = $container->getProject();
        $directories = [];
        foreach ($this->watchDirectories as $directory) {
            $directories[] = $project->resolve($directory);
        }

        if ($project->mode->isDevelopment()) {
            $directories[] = $project->entrypoint;
        }

        $watcherConfiguration = Configuration::createForDirectories($directories)
            ->withPollInterval($this->watchInterval);

        if ($this->watchExtensions !== []) {
            $watcherConfiguration = $watcherConfiguration->withExtensions($this->watchExtensions);
        }

        return new ClusterCommand(
            $container->getTyped(ClusterInterface::class, ClusterInterface::class),
            $container->getProject(),
            $watcherConfiguration,
        );
    }
}
