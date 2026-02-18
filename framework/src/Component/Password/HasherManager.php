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

namespace Neu\Component\Password;

use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Neu\Component\DependencyInjection\ServiceLocatorInterface;
use Override;

/**
 * A password hasher manager implementation.
 */
final readonly class HasherManager implements HasherManagerInterface
{
    /**
     * The identifier for the default password hasher.
     *
     * @var non-empty-string
     */
    private string $defaultStoreId;

    /**
     * The service locator used to create password hasher instances.
     *
     * @var ServiceLocatorInterface<HasherInterface>
     */
    private ServiceLocatorInterface $locator;

    /**
     * Create a new {@see HasherManager} instance.
     *
     * @param non-empty-string $defaultHasherId The identifier for the default password hasher.
     * @param ServiceLocatorInterface<HasherInterface> $locator The service locator used to create password hasher instances.
     */
    public function __construct(string $defaultHasherId, ServiceLocatorInterface $locator)
    {
        $this->defaultStoreId = $defaultHasherId;
        $this->locator = $locator;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getDefaultHasher(): HasherInterface
    {
        return $this->getHasher($this->defaultStoreId);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function hasHasher(string $identifier): bool
    {
        return $this->locator->has($identifier);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getHasher(string $identifier): HasherInterface
    {
        try {
            return $this->locator->get($identifier);
        } catch (ServiceNotFoundException $e) {
            throw new Exception\HasherNotFoundException($identifier, previous: $e);
        } catch (ExceptionInterface $e) {
            throw new Exception\RuntimeException('Failed to load the password hasher.', previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getAvailableHashers(): array
    {
        $services = $this->locator->getAvailableServices();
        $hashers = [];

        foreach ($services as $identifier) {
            try {
                $hashers[$identifier] = $this->getHasher($identifier);
            } catch (Exception\HasherNotFoundException) {
                // unreachable
            }
        }

        return $hashers;
    }
}
