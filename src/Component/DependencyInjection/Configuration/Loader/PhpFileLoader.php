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
use Neu\Component\DependencyInjection\Exception\InvalidConfigurationException;
use Psl\Filesystem;
use Psl\Str;
use Psl\Type;

/**
 * @implements LoaderInterface<non-empty-string>
 */
final class PhpFileLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function load(mixed $resource): DocumentInterface
    {
        /** @var mixed $data */
        $data = (static function () use ($resource): mixed {
            /** @psalm-suppress UnresolvableInclude */
            return @require $resource;
        })();

        try {
            $data = Type\dict(Type\string(), Type\mixed())->coerce($data);
        } catch (Type\Exception\CoercionException $previous) {
            throw new InvalidConfigurationException(
                'failed to coerce php resource file "' . $resource . '".',
                previous: $previous
            );
        }

        return new Document($data);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function supports(mixed $resource): bool
    {
        if (!Type\non_empty_string()->matches($resource) || !Str\ends_with($resource, '.php')) {
            return false;
        }

        return Filesystem\is_file($resource) && Filesystem\is_readable($resource);
    }
}
