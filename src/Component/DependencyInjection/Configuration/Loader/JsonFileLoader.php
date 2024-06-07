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
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Psl\File;
use Psl\Filesystem;
use Psl\Json;
use Psl\Str;
use Psl\Type;

/**
 * @implements LoaderInterface<non-empty-string>
 */
final class JsonFileLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     */
    public function load(mixed $resource): DocumentInterface
    {
        try {
            $content = File\read($resource);
        } catch (File\Exception\ExceptionInterface $previous) {
            throw new RuntimeException(
                'failed to read json resource file "' . $resource . '".',
                previous: $previous
            );
        }

        try {
            $data = Json\typed($content, Type\dict(Type\string(), Type\mixed()));
        } catch (Json\Exception\DecodeException $previous) {
            throw new InvalidConfigurationException(
                'failed to decode json resource file "' . $resource . '".',
                previous: $previous
            );
        }

        return new Document($data);
    }

    /**
     * @inheritDoc
     */
    public function supports(mixed $resource): bool
    {
        if (!Type\non_empty_string()->matches($resource) || !Str\ends_with($resource, '.json')) {
            return false;
        }

        return Filesystem\is_file($resource) && Filesystem\is_readable($resource);
    }
}
