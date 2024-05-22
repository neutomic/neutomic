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

namespace Neu\Component\Configuration\DependencyInjection;

use Amp\File;
use Neu\Component\Configuration\ConfigurationContainer;
use Neu\Component\Configuration\ConfigurationContainerInterface;
use Neu\Component\Configuration\Resolver\Resolver;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\DependencyInjection\Project;
use Psl\Filesystem;
use Psl\Str;
use Psl\Type;

use function array_walk_recursive;

final readonly class ConfigurationExtension implements ExtensionInterface
{
    public function register(ContainerBuilderInterface $container): void
    {
        $resolver = Resolver::create(recursive: false);
        $configuration = new ConfigurationContainer([]);
        foreach ($this->getResources($container->getProject()) as $resource) {
            if (!File\isDirectory($resource)) {
                continue;
            }

            $configuration = $configuration->merge(
                $resolver->resolve($resource)->load($resource),
            );
        }

        $configuration = $this->resolvePaths($container->getProject(), $configuration);

        $container->addConfiguration($configuration);
    }

    /**
     * Get the resources to load.
     *
     * @return list<non-empty-string> The resources to load.
     */
    private function getResources(Project $project): array
    {
        $config = $project->config;
        if (null === $config) {
            return [];
        }

        if (File\isDirectory($config)) {
            $resources = [$config];
            foreach ($project->mode->getPossibleValues() as $mode) {
                $resources[] = $config . Filesystem\SEPARATOR . $mode;
            }

            return $resources;
        }

        if (File\isFile($config)) {
            $resources = [$config];

            // if $config is foo.json, we need to load foo.{mode}.json
            $extension = Filesystem\get_extension($config);

            if (null === $extension) {
                return $resources;
            }

            $basename = Filesystem\get_basename($config);
            foreach ($project->mode->getPossibleValues() as $mode) {
                $resources[] = $basename . '.' . $mode . '.' . $extension;
            }

            return $resources;
        }

        return [$config];
    }

    private function resolvePaths(Project $project, ConfigurationContainerInterface $container): ConfigurationContainerInterface
    {
        $entries = $container->getAll();
        array_walk_recursive($entries, static function (mixed &$entry) use ($project): void {
            if (Type\non_empty_string()->matches($entry) && Str\contains($entry, '%')) {
                $entry = $project->resolve($entry);
            }
        });

        return new ConfigurationContainer($entries);
    }
}
