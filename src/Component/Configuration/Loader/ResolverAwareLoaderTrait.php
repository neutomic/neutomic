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

namespace Neu\Component\Configuration\Loader;

use Neu\Component\Configuration\Exception\LogicException;
use Neu\Component\Configuration\Resolver\ResolverInterface;

/**
 * @psalm-require-implements ResolverAwareLoaderInterface
 */
trait ResolverAwareLoaderTrait
{
    /**
     * The resolver instance.
     */
    private null|ResolverInterface $resolver = null;

    /**
     * @inheritDoc
     */
    public function setResolver(ResolverInterface $resolver): void
    {
        $this->resolver = $resolver;
    }

    /**
     * @throws LogicException If the resolver has not been set.
     */
    protected function getResolver(): ResolverInterface
    {
        if (null === $this->resolver) {
            throw new LogicException(
                'Resolver has not been set on the "' . static::class . '" loader, make sure to call "' . static::class . '::setResolver()" before attempting to load resources.',
            );
        }

        return $this->resolver;
    }
}
