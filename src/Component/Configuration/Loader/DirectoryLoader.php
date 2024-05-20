<?php

declare(strict_types=1);

namespace Neu\Component\Configuration\Loader;

use Neu\Component\Configuration\ConfigurationContainer;
use Neu\Component\Configuration\ConfigurationContainerInterface;
use Neu\Component\Configuration\Exception\LogicException;
use Neu\Component\Configuration\Exception\NoSupportiveLoaderException;
use Psl\Filesystem;
use Psl\Type;

/**
 * @implements ResolverAwareLoaderInterface<non-empty-string>
 */
final class DirectoryLoader implements ResolverAwareLoaderInterface
{
    use ResolverAwareLoaderTrait;

    public function __construct(
        private readonly bool $recursive = true,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver has not been set.
     * @throws Filesystem\Exception\ExceptionInterface If failed to read the directory.
     */
    public function load(mixed $resource): ConfigurationContainerInterface
    {
        /** @var ConfigurationContainer<array-key> $container */
        $container = new ConfigurationContainer([]);
        $resolver = $this->getResolver();
        foreach (Filesystem\read_directory($resource) as $node) {
            if (Filesystem\is_file($node)) {
                try {
                    $loader = $resolver->resolve($node);
                } catch (NoSupportiveLoaderException) {
                    continue;
                }

                $container = $container->merge($loader->load($node));
            } elseif ($this->recursive) {
                $container = $container->merge($this->load($node));
            }
        }

        return $container;
    }

    /**
     * @inheritDoc
     */
    public function supports(mixed $resource): bool
    {
        if (!Type\non_empty_string()->matches($resource)) {
            return false;
        }

        return Filesystem\is_directory($resource) && Filesystem\is_readable($resource);
    }
}
