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

namespace Neu\Component\Broadcast;

use Neu\Component\Broadcast\Exception\HubNotFoundException;
use Neu\Component\Broadcast\Exception\RuntimeException;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Neu\Component\DependencyInjection\ServiceLocatorInterface;

final class HubManager implements HubManagerInterface
{
    /**
     * The identifier for the default hub.
     *
     * @var non-empty-string
     */
    private string $defaultHubId;

    /**
     * The service locator used to create hub instances.
     *
     * @var ServiceLocatorInterface<HubInterface>
     */
    private ServiceLocatorInterface $locator;

    /**
     * Create a new {@see HubManager} instance.
     *
     * @param non-empty-string $defaultHubId The identifier for the default hub.
     * @param ServiceLocatorInterface<HubInterface> $locator The service locator used to create hub instances.
     */
    public function __construct(string $defaultHubId, ServiceLocatorInterface $locator)
    {
        $this->defaultHubId = $defaultHubId;
        $this->locator = $locator;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getDefaultHub(): HubInterface
    {
        return $this->getHub($this->defaultHubId);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function hasHub(string $identifier): bool
    {
        return $this->locator->has($identifier);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getHub(string $identifier): HubInterface
    {
        try {
            return $this->locator->get($identifier);
        } catch (ServiceNotFoundException $exception) {
            throw HubNotFoundException::forHub($identifier, $exception);
        } catch (ExceptionInterface $exception) {
            throw new RuntimeException('An error occurred while retrieving the hub.', previous: $exception);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getAvailableHubs(): array
    {
        $hubs = [];
        $services = $this->locator->getAvailableServices();
        foreach ($services as $service) {
            try {
                $hubs[] = $this->getHub($service);
            } catch (HubNotFoundException) {
                // unreachable
            }
        }

        return $hubs;
    }
}
