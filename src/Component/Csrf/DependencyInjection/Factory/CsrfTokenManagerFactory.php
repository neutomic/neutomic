<?php

declare(strict_types=1);

namespace Neu\Component\Csrf\DependencyInjection\Factory;

use Neu\Component\Csrf\CsrfTokenManager;
use Neu\Component\Csrf\Generator\CsrfTokenGeneratorInterface;
use Neu\Component\Csrf\Storage\CsrfTokenStorageInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see CsrfTokenManager} instance.
 *
 * @implements FactoryInterface<CsrfTokenManager>
 */
final readonly class CsrfTokenManagerFactory implements FactoryInterface
{
    /**
     * The service identifier of the {@see CsrfTokenGeneratorInterface} implementation to use.
     *
     * @var non-empty-string
     */
    private string $generator;

    /**
     * The service identifier of the {@see CsrfTokenStorageInterface} implementation to use.
     *
     * @var non-empty-string
     */
    private string $storage;

    /**
     * Creates a new {@see CsrfTokenManagerFactory} instance.
     *
     * @param non-empty-string|null $generator The service identifier of the {@see CsrfTokenGeneratorInterface} implementation
     *                                         to use, if null, the default implementation is used.
     * @param non-empty-string|null $storage The service identifier of the {@see CsrfTokenStorageInterface} implementation
     *                                       to use, if null, the default implementation is used.
     */
    public function __construct(?string $generator = null, ?string $storage = null)
    {
        $this->generator = $generator ?? CsrfTokenGeneratorInterface::class;
        $this->storage = $storage ?? CsrfTokenStorageInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): CsrfTokenManager
    {
        $generator = $container->getTyped($this->generator, CsrfTokenGeneratorInterface::class);
        $storage = $container->getTyped($this->storage, CsrfTokenStorageInterface::class);

        return new CsrfTokenManager($generator, $storage);
    }
}
