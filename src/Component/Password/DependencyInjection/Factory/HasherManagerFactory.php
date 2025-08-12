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

namespace Neu\Component\Password\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Password\HasherInterface;
use Neu\Component\Password\HasherManager;

/**
 * Factory for creating a {@see HasherManager} instance.
 *
 * @implements FactoryInterface<HasherManager>
 */
final readonly class HasherManagerFactory implements FactoryInterface
{
    /**
     * The identifier for the default hasher.
     */
    private string $defaultHasherId;

    /**
     * An array of container services identifiers, indexed by the locator service identifier.
     *
     * @var array<non-empty-string, non-empty-string>
     */
    private array $services;

    /**
     * Create a new {@see HasherManagerFactory} instance.
     *
     * @param string $defaultHasherId The identifier for the default hasher.
     * @param array<non-empty-string, non-empty-string> $services An array of container services identifiers, indexed by the locator service identifier.
     */
    public function __construct(string $defaultHasherId, array $services)
    {
        $this->services = $services;
        $this->defaultHasherId = $defaultHasherId;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container): HasherManager
    {
        $locator = $container->getLocator(HasherInterface::class, $this->services);
        if (!$locator->has($this->defaultHasherId)) {
            throw new RuntimeException('The default hasher is not defined.');
        }

        return new HasherManager($this->defaultHasherId, $locator);
    }
}
