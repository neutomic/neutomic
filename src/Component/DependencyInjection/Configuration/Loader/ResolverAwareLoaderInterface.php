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

namespace Neu\Component\DependencyInjection\Configuration\Loader;

use Neu\Component\DependencyInjection\Configuration\Resolver\ResolverInterface;

/**
 * @template ResourceType
 *
 * @extends LoaderInterface<ResourceType>
 */
interface ResolverAwareLoaderInterface extends LoaderInterface
{
    /**
     * Set the resolver to be used for loading sub-resources.
     */
    public function setResolver(ResolverInterface $resolver): void;
}
