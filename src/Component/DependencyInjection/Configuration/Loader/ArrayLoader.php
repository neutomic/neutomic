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

use Neu\Component\DependencyInjection\Configuration\Document;
use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Psl\Type;
use Override;

/**
 * @implements LoaderInterface<array<array-key, mixed>>
 */
final class ArrayLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function load(mixed $resource): DocumentInterface
    {
        return new Document($resource);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function supports(mixed $resource): bool
    {
        return Type\mixed_dict()->matches($resource);
    }
}
