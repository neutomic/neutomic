<?php

declare(strict_types=1);

namespace Neu\Component\Csrf\DependencyInjection\Factory\Storage;

use Neu\Component\Csrf\Storage\SessionCsrfTokenStorage;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see SessionCsrfTokenStorage} instance.
 *
 * @implements FactoryInterface<SessionCsrfTokenStorage>
 */
final readonly class SessionCsrfTokenStorageFactory implements FactoryInterface
{
    /**
     * The prefix to use for the session keys.
     *
     * @var non-empty-string
     */
    private string $prefix;

    /**
     * Creates a new {@see SessionCsrfTokenStorageFactory} instance.
     *
     * @param non-empty-string|null $prefix The prefix to use for the session keys.
     */
    public function __construct(?string $prefix = null)
    {
        $this->prefix = $prefix ?? SessionCsrfTokenStorage::DEFAULT_PREFIX;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): SessionCsrfTokenStorage
    {
        return new SessionCsrfTokenStorage($this->prefix);
    }
}
