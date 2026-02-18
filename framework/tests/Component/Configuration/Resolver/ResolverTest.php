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

namespace Neu\Tests\Component\Configuration\Resolver;

use Neu\Component\DependencyInjection\Configuration\Loader\LoaderInterface;
use Neu\Component\DependencyInjection\Configuration\Loader\ResolverAwareLoaderInterface;
use Neu\Component\DependencyInjection\Configuration\Resolver\Resolver;
use Neu\Component\DependencyInjection\Exception\NoSupportiveLoaderException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class ResolverTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testResolving(): void
    {
        $loader1 = $this->createMock(LoaderInterface::class);
        $loader2 = $this->createMock(LoaderInterface::class);

        $resolver = new Resolver([$loader1, $loader2]);

        $loader1->expects(static::once())->method('supports')->with('foo.json')->willReturn(false);
        $loader2->expects(static::once())->method('supports')->with('foo.json')->willReturn(true);

        $loader = $resolver->resolve('foo.json');

        static::assertSame($loader2, $loader);
    }

    /**
     * @throws Exception
     */
    public function testResolvingWithNoSupportiveLoaders(): void
    {
        $loader1 = $this->createMock(LoaderInterface::class);
        $loader2 = $this->createMock(LoaderInterface::class);

        $resolver = new Resolver([$loader1, $loader2]);

        $loader1->expects(static::once())->method('supports')->with('file.yaml')->willReturn(false);
        $loader2->expects(static::once())->method('supports')->with('file.yaml')->willReturn(false);

        $this->expectException(NoSupportiveLoaderException::class);
        $this->expectExceptionMessage('unable to load resource "file.yaml": no supportive loader found.');

        $resolver->resolve('file.yaml');
    }

    /**
     * @throws Exception
     */
    public function testResolvingComplexResourceWithNoSupportiveLoaders(): void
    {
        $loader1 = $this->createMock(LoaderInterface::class);
        $loader2 = $this->createMock(LoaderInterface::class);

        $resolver = new Resolver([]);
        $resolver->addLoader($loader1);
        $resolver->addLoader($loader2);

        $loader1->expects(static::once())->method('supports')->with([])->willReturn(false);
        $loader2->expects(static::once())->method('supports')->with([])->willReturn(false);

        $this->expectException(NoSupportiveLoaderException::class);
        $this->expectExceptionMessage('unable to load resource "{array}": no supportive loader found.');

        $resolver->resolve([]);
    }

    /**
     * @throws Exception
     */
    public function testResolvingWithResolverAwareLoader(): void
    {
        $loader1 = $this->createMock(LoaderInterface::class);
        $loader2 = $this->createMock(ResolverAwareLoaderInterface::class);

        $resolver = new Resolver([$loader1, $loader2]);

        $loader1->expects(static::once())->method('supports')->with('foo.json')->willReturn(false);
        $loader2->expects(static::once())->method('supports')->with('foo.json')->willReturn(true);
        $loader2->expects(static::once())->method('setResolver')->with($resolver);

        $loader = $resolver->resolve('foo.json');

        static::assertSame($loader2, $loader);
    }

    /**
     * @throws Exception
     */
    public function testSetResolverIsNotCalledOnNonSupportiveLoaders(): void
    {
        $loader1 = $this->createMock(ResolverAwareLoaderInterface::class);
        $loader2 = $this->createMock(ResolverAwareLoaderInterface::class);

        $resolver = new Resolver([$loader1, $loader2]);

        $loader1->expects(static::once())->method('supports')->with('foo.json')->willReturn(false);
        $loader2->expects(static::once())->method('supports')->with('foo.json')->willReturn(true);
        $loader1->expects(static::never())->method('setResolver');
        $loader2->expects(static::once())->method('setResolver')->with($resolver);

        $loader = $resolver->resolve('foo.json');

        static::assertSame($loader2, $loader);
    }
}
