<?php

declare(strict_types=1);

namespace Neu\Component\Configuration\Resolver;

use Neu\Component\Configuration\Exception\NoSupportiveLoaderException;
use Neu\Component\Configuration\Loader;
use Neu\Component\Configuration\Loader\LoaderInterface;
use Neu\Component\Configuration\Loader\ResolverAwareLoaderInterface;

use function get_debug_type;
use function is_scalar;
use function sprintf;

final class Resolver implements ResolverInterface
{
    /**
     * @param list<LoaderInterface> $loaders
     */
    public function __construct(
        private array $loaders = [],
    ) {
    }

    /**
     * Create a new instance of the resolver.
     *
     * The resolver is pre-configured with the following loaders:
     *
     * - {@see Loader\PHPFileLoader}
     * - {@see Loader\JsonFileLoader}
     * - {@see Loader\YamlFileLoader}
     * - {@see Loader\DirectoryLoader}
     *
     * Additional loaders can be added using {@see Resolver::addLoader()}.
     *
     * @return self
     */
    public static function create(bool $recursive = true): self
    {
        return new self([
            new Loader\PHPFileLoader(),
            new Loader\JsonFileLoader(),
            new Loader\YamlFileLoader(),
            new Loader\DirectoryLoader($recursive),
        ]);
    }

    /**
     * @param LoaderInterface<mixed> $loader
     */
    public function addLoader(LoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * @inheritDoc
     *
     * @template T
     *
     * @param T $resource
     *
     * @return LoaderInterface<T>
     */
    public function resolve(mixed $resource): LoaderInterface
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($resource)) {
                if ($loader instanceof ResolverAwareLoaderInterface) {
                    $loader->setResolver($this);
                }

                /** @var LoaderInterface<T> */
                return $loader;
            }
        }

        throw new NoSupportiveLoaderException(sprintf(
            'Unable to load resource "%s": no supportive loader found.',
            $this->getResourceStringRepresentation($resource),
        ));
    }

    private function getResourceStringRepresentation(mixed $resource): string
    {
        if (is_scalar($resource)) {
            return (string) $resource;
        }

        return sprintf('{%s}', get_debug_type($resource));
    }
}
