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

namespace Neu\Component\Cache\DependencyInjection\Factory\Driver;

use Neu\Component\Cache\Driver\LocalDriver;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Override;

/**
 * A factory for creating a {@see LocalDriver} instance.
 *
 * @implements FactoryInterface<LocalDriver>
 */
final readonly class LocalDriverFactory implements FactoryInterface
{
    /**
     * @var positive-int
     */
    private int $pruneInterval;

    /**
     * @var null|positive-int
     */
    private null|int $size;

    /**
     * @param positive-int $pruneInterval The interval in seconds to prune the cache.
     * @param null|positive-int $size The maximum size of the cache.
     */
    public function __construct(null|int $pruneInterval = null, null|int $size = null)
    {
        $this->pruneInterval = $pruneInterval ?? LocalDriver::PRUNE_INTERVAL;
        $this->size = $size;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(ContainerInterface $container): LocalDriver
    {
        return new LocalDriver($this->pruneInterval, $this->size);
    }
}
