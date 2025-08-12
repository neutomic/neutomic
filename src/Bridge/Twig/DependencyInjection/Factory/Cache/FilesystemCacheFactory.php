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
     *
     * @var non-empty-string
     */
    private string $cache;

    /**
     * The cache options.
     */
    private int $options;

    /**
     * @param non-empty-string $cache The cache directory, or null to disable caching.
     * @param null|int $options The cache options.
     */
    public function __construct(string $cache, null|int $options = null)
    {
        $this->cache = $cache;
        $this->options = $options ?? 0;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container): FilesystemCache
    {
        return new FilesystemCache($this->cache, $this->options);
    }
}
