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

namespace Neu\Component\Csrf\DependencyInjection\Factory\Storage;

use Neu\Component\Csrf\Storage\SessionCsrfTokenStorage;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Override;

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
    public function __construct(null|string $prefix = null)
    {
        $this->prefix = $prefix ?? SessionCsrfTokenStorage::DEFAULT_PREFIX;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(ContainerInterface $container): SessionCsrfTokenStorage
    {
        return new SessionCsrfTokenStorage($this->prefix);
    }
}
