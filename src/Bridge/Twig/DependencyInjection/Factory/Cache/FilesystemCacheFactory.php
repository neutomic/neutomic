<?php

declare(strict_types=1);

namespace Neu\Bridge\Twig\DependencyInjection\Factory\Cache;

use Neu\Bridge\Twig\Cache\FilesystemCache;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * @implements FactoryInterface<FilesystemCache>
 */
final readonly class FilesystemCacheFactory implements FactoryInterface
{
    /**
     * The cache directory, or null to disable caching.
     */
    private string $cache;

    /**
     * The cache options.
     */
    private int $options;

    /**
     * @param null|string $cache The cache directory, or null to disable caching.
     * @param null|int $options The cache options.
     */
    public function __construct(string $cache, null|int $options)
    {
        $this->cache = $cache;
        $this->options = $options ?? 0;
    }

    public function __invoke(ContainerInterface $container): FilesystemCache
    {
        return new FilesystemCache($this->cache, $this->options);
    }
}
