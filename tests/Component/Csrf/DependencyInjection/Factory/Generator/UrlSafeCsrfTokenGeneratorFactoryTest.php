<?php

declare(strict_types=1);

namespace Neu\Tests\Component\Csrf\DependencyInjection\Factory\Generator;

use Neu\Component\Csrf\DependencyInjection\Factory\Generator\UrlSafeCsrfTokenGeneratorFactory;
use Neu\Component\Csrf\Generator\UrlSafeCsrfTokenGenerator;
use Neu\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;

final class UrlSafeCsrfTokenGeneratorFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $factory = new UrlSafeCsrfTokenGeneratorFactory();
        $generator = $factory($container);

        static::assertInstanceOf(UrlSafeCsrfTokenGenerator::class, $generator);
    }
}
