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

namespace Neu\Component\DependencyInjection\Configuration\Loader;

use Neu\Component\DependencyInjection\Configuration\CombineStrategy;
use Neu\Component\DependencyInjection\Configuration\Document;
use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Exception\NoSupportiveLoaderException;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Psl\Filesystem;
use Psl\Type;
use Psl\Vec;

/**
 * A loader that loads documents from a directory.
 *
 * @implements ResolverAwareLoaderInterface<non-empty-string>
 */
final class DirectoryLoader implements ResolverAwareLoaderInterface
{
    use ResolverAwareLoaderTrait;

    /**
     * The strategy to combine the loaded documents.
     */
    private readonly CombineStrategy $strategy;

    /**
     * Create a new {@see DirectoryLoader} instance.
     *
     * @param CombineStrategy $strategy The strategy to combine the loaded documents.
     */
    public function __construct(CombineStrategy $strategy = CombineStrategy::ReplaceRecursive)
    {
        $this->strategy = $strategy;
    }

    /**
     * @inheritDoc
     */
    public function load(mixed $resource): DocumentInterface
    {
        try {
            $document = new Document([]);
            $resolver = $this->getResolver();
            foreach (Vec\sort(Filesystem\read_directory($resource)) as $node) {
                if (Filesystem\is_file($node)) {
                    try {
                        $loader = $resolver->resolve($node);
                    } catch (NoSupportiveLoaderException) {
                        continue;
                    }

                    $document = match ($this->strategy) {
                        CombineStrategy::Merge => $document->merge(
                            $loader->load($node),
                            recursive: false,
                        ),
                        CombineStrategy::MergeRecursive => $document->merge(
                            $loader->load($node),
                        ),
                        CombineStrategy::Replace => $document->replace(
                            $loader->load($node),
                            recursive: false,
                        ),
                        CombineStrategy::ReplaceRecursive => $document->replace(
                            $loader->load($node),
                        ),
                    };
                }
            }
        } catch (Filesystem\Exception\ExceptionInterface $previous) {
            throw new RuntimeException(
                'failed to read directory "' . $resource . '".',
                previous: $previous
            );
        }

        return $document;
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
