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
use Psl\Str;
use Psl\Type;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @implements LoaderInterface<non-empty-string>
 */
final class YamlFileLoader implements LoaderInterface
{
    private const int YAML_PARSE_FLAGS =
        Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE |
        Yaml::PARSE_CONSTANT
    ;

    /**
     * @inheritDoc
     */
    #[\Override]
    public function load(mixed $resource): DocumentInterface
    {
        try {
            $content = File\read($resource);
        } catch (File\Exception\ExceptionInterface $previous) {
            throw new RuntimeException(
                'failed to read yaml resource file "' . $resource . '".',
                previous: $previous
            );
        }

        try {
            /** @psalm-suppress MixedAssignment */
            $data = Yaml::parse($content, self::YAML_PARSE_FLAGS);
        } catch (ParseException $previous) {
            throw new InvalidConfigurationException(
                'failed to decode yaml resource file "' . $resource . '".',
                previous: $previous
            );
        }

        try {
            $data = Type\dict(Type\string(), Type\mixed())->coerce($data);
        } catch (Type\Exception\CoercionException $previous) {
            throw new InvalidConfigurationException(
                'failed to coerce yaml resource file "' . $resource . '".',
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
        if (!Type\non_empty_string()->matches($resource) || !(Str\ends_with($resource, '.yaml') || Str\ends_with($resource, '.yml'))) {
            return false;
        }

        return Filesystem\is_file($resource) && Filesystem\is_readable($resource);
    }
}
