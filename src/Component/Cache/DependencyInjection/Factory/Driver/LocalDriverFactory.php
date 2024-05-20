<?php

declare(strict_types=1);

namespace Neu\Component\Cache\DependencyInjection\Factory\Driver;

use Neu\Component\Cache\Driver\LocalDriver;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * A factory for creating a {@see LocalDriver} instance.
 *
 * @implements FactoryInterface<LocalDriver>
 */
final readonly class LocalDriverFactory implements FactoryInterface
{
    private int $pruneInterval;
    private ?int $size;

    /**
     * @param int $pruneInterval The interval in seconds to prune the cache.
     * @param null|int $size The maximum size of the cache.
     */
    public function __construct(?int $pruneInterval = null, ?int $size = null)
    {
        $this->pruneInterval = $pruneInterval ?? LocalDriver::PRUNE_INTERVAL;
        $this->size = $size;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): LocalDriver
    {
        return new LocalDriver($this->pruneInterval, $this->size);
    }
}
