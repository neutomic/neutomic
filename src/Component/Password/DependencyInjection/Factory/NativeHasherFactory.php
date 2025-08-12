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
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Password\NativeHasher;

/**
 * A factory for creating native password hashers.
 *
 * @implements FactoryInterface<NativeHasher>
 *
 * @psalm-import-type Algorithm from NativeHasher as HashingAlgorithm
 * @psalm-import-type Options from NativeHasher as HashingOptions
 */
final readonly class NativeHasherFactory implements FactoryInterface
{
    /**
     * The hashing algorithm.
     *
     * @var HashingAlgorithm
     */
    private string $algorithm;

    /**
     * The hashing algorithm options.
     *
     * @var HashingOptions
     */
    private array $options;

    /**
     * Construct a new {@see NativeHasherFactory} instance.
     *
     * @param null|HashingAlgorithm $algorithm The hashing algorithm.
     * @param null|HashingOptions $options The hashing algorithm options.
     */
    public function __construct(null|string $algorithm = null, null|array $options = null)
    {
        $this->algorithm = $algorithm ?? NativeHasher::DEFAULT_ALGORITHM;
        $this->options = $options ?? NativeHasher::DEFAULT_OPTIONS;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): NativeHasher
    {

        return new NativeHasher($this->algorithm, $this->options);
    }
}
