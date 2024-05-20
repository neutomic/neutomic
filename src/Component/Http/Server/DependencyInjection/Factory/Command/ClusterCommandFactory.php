<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server\DependencyInjection\Factory\Command;

use Assess\Configuration;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Server\ClusterInterface;
use Neu\Component\Http\Server\Command\ClusterCommand;

/**
 * @implements FactoryInterface<ClusterCommand>
 */
final readonly class ClusterCommandFactory implements FactoryInterface
{
    /**
     * The interval in seconds to poll for changes.
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
    public function __construct(?float $watchInterval = null, ?array $watchDirectories = null, ?array $watchExtensions = null)
    {
        $this->watchInterval = $watchInterval ?? Configuration::DEFAULT_POLL_INTERVAL;
        $this->watchDirectories = $watchDirectories ?? [];
        $this->watchExtensions = $watchExtensions ?? [];
    }

    public function __invoke(ContainerInterface $container): ClusterCommand
    {
        $project = $container->getProject();
        $directories = [];
        foreach ($this->watchDirectories as $directory) {
            $directories[] = $project->resolve($directory);
        }

        if ($project->mode->isDevelopment()) {
            $directories[] = $project->entrypoint;
            if (null !== $project->source) {
                $directories[] = $project->source;
            }

            if (null !== $project->config) {
                $directories[] = $project->config;
            }
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
