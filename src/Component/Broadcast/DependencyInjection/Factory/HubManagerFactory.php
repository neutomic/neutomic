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

namespace Neu\Component\Broadcast\DependencyInjection\Factory;

use Neu\Component\Broadcast\HubInterface;
use Neu\Component\Broadcast\HubManager;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Override;

/**
 * Factory for creating a {@see HubManager} instance.
 *
 * @implements FactoryInterface<HubManager>
 */
final readonly class HubManagerFactory implements FactoryInterface
{
    /**
     * The identifier for the default hub.
     */
    private string $defaultHubId;

    /**
     * An array of container services identifiers, indexed by the locator service identifier.
     *
     * @var array<non-empty-string, non-empty-string>
     */
    private array $services;

    /**
     * Create a new {@see HubManagerFactory} instance.
     *
     * @param string $defaultHubId The identifier for the default hub.
     * @param array<non-empty-string, non-empty-string> $services An array of container services identifiers, indexed by the locator service identifier.
     */
    public function __construct(string $defaultHubId, array $services)
    {
        $this->defaultHubId = $defaultHubId;
        $this->services = $services;
    }

    #[Override]
    public function __invoke(ContainerInterface $container): HubManager
    {
        $locator = $container->getLocator(HubInterface::class, $this->services);
        if (!$locator->has($this->defaultHubId)) {
            throw new RuntimeException('The default hub is not defined.');
        }

        return new HubManager($this->defaultHubId, $locator);
    }
}
