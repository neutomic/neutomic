<?php

declare(strict_types=1);

namespace Neu\Bridge\Twig\DependencyInjection\Factory;

use Neu\Bridge\Twig\Loader\FilesystemLoader;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * @implements FactoryInterface<FilesystemLoader>
 */
final readonly class FilesystemLoaderFactory implements FactoryInterface
{
    /**
     * @var array<non-empty-string, null|non-empty-string>
     */
    private array $paths;

    /**
     * @var null|non-empty-string
     */
    private ?string $root;

    /**
     * @param array<non-empty-string, null|non-empty-string> $paths
     * @param null|non-empty-string $root
     */
    public function __construct(?array $paths, ?string $root)
    {
        $this->paths = $paths ?? [];
        $this->root = $root;
    }

    public function __invoke(ContainerInterface $container): FilesystemLoader
    {
        if ($this->root !== null) {
            $root = $container->getProject()->resolve($this->root);
        } else {
            $root = $container->getProject()->directory;
        }

        $loader = new FilesystemLoader([], $root);
        foreach ($this->paths as $path => $namespace) {
            $path = $container->getProject()->resolve($path);

            $loader->addPath($path, $namespace ?? FilesystemLoader::MAIN_NAMESPACE);
        }

        return $loader;
    }
}
