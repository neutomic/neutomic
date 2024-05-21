<?php

declare(strict_types=1);

namespace Neu\Component\Csrf\DependencyInjection\Factory\Generator;

use Neu\Component\Csrf\Generator\UrlSafeCsrfTokenGenerator;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see UrlSafeCsrfTokenGenerator} instance.
 *
 * @implements FactoryInterface<UrlSafeCsrfTokenGenerator>
 */
final readonly class UrlSafeCsrfTokenGeneratorFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): UrlSafeCsrfTokenGenerator
    {
        return new UrlSafeCsrfTokenGenerator();
    }
}
