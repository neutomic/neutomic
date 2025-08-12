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

namespace Neu\Component\DependencyInjection\Configuration\Resolver;

use Neu\Component\DependencyInjection\Configuration\CombineStrategy;
use Neu\Component\DependencyInjection\Configuration\Loader\ArrayLoader;
use Neu\Component\DependencyInjection\Configuration\Loader\DirectoryLoader;
use Neu\Component\DependencyInjection\Configuration\Loader\DocumentLoader;
use Neu\Component\DependencyInjection\Configuration\Loader\JsonFileLoader;
use Neu\Component\DependencyInjection\Configuration\Loader\LoaderInterface;
use Neu\Component\DependencyInjection\Configuration\Loader\PhpFileLoader;
use Neu\Component\DependencyInjection\Configuration\Loader\ResolverAwareLoaderInterface;
use Neu\Component\DependencyInjection\Configuration\Loader\YamlFileLoader;
use Neu\Component\DependencyInjection\Exception\NoSupportiveLoaderException;
use Psl\Str;
use Psl\Type;

use function get_debug_type;

final class Resolver implements ResolverInterface
{
    /**
     * The list of loaders.
     *
     * @var list<LoaderInterface>
     */
    private array $loaders;

    /**
     * Create a new {@see Resolver} instance.
     *
     * @param list<LoaderInterface> $loaders The list of loaders.
     */
    public function __construct(array $loaders)
    {
        $this->loaders = $loaders;
    }

    /**
     * Create a new {@see Resolver} instance with the default loaders.
     *
     * The resolver is pre-configured with the following loaders:
     *
     * - {@see DocumentLoader}
     * - {@see ArrayLoader}
     * - {@see PhpFileLoader}
     * - {@see JsonFileLoader}
     * - {@see YamlFileLoader}
     * - {@see DirectoryLoader}
     *
     * Additional loaders can be added using {@see Resolver::addLoader()}.
     *
     * @param CombineStrategy $strategy The combine strategy to use for the {@see DirectoryLoader}.
     *
     * @return self
     */
    public static function create(CombineStrategy $strategy = CombineStrategy::ReplaceRecursive): self
    {
        return new self([
            new DocumentLoader(),
            new ArrayLoader(),
            new PhpFileLoader(),
            new JsonFileLoader(),
            new YamlFileLoader(),
            new DirectoryLoader($strategy),
        ]);
    }

    /**
     * Add a loader to the resolver.
     *
     * @param LoaderInterface $loader The loader to add.
     */
    public function addLoader(LoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * @inheritDoc
     *
     * @template ResourceType
     *
     * @param ResourceType $resource
     *
     * @return LoaderInterface<ResourceType>
     */
    #[\Override]
    public function resolve(mixed $resource): LoaderInterface
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($resource)) {
                if ($loader instanceof ResolverAwareLoaderInterface) {
                    $loader->setResolver($this);
                }

                /** @var LoaderInterface<ResourceType> */
                return $loader;
            }
        }

        throw new NoSupportiveLoaderException(sprintf(
            'unable to load resource "%s": no supportive loader found.',
            $this->getResourceStringRepresentation($resource),
        ));
    }

    /**
     * Get the string representation of the resource.
     *
     * @param mixed $resource The resource to get the string representation of.
     *
     * @return string The string representation of the resource.
     */
    private function getResourceStringRepresentation(mixed $resource): string
    {
        if (Type\scalar()->matches($resource)) {
            return (string) $resource;
        }

        return Str\format('{%s}', get_debug_type($resource));
    }
}
