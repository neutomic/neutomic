<?php

declare(strict_types=1);

namespace Neu\Component\Configuration\Loader;

use Neu\Component\Configuration\ConfigurationContainer;
use Neu\Component\Configuration\ConfigurationContainerInterface;
use Neu\Component\Configuration\Exception\InvalidConfigurationException;
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
     *
     * @throws File\Exception\RuntimeException If unable to read $resource.
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public function load(mixed $resource): ConfigurationContainerInterface
    {
        $content = File\read($resource);

        try {
            $data = Yaml::parse($content, self::YAML_PARSE_FLAGS);
        } catch (ParseException $previous) {
            throw new InvalidConfigurationException(
                'Failed to decode yaml resource file "' . $resource . '".',
                previous: $previous
            );
        }

        try {
            $data = Type\dict(Type\string(), Type\mixed())->coerce($data);
        } catch (Type\Exception\CoercionException $previous) {
            throw new InvalidConfigurationException(
                'Failed to coerce yaml resource file "' . $resource . '".',
                previous: $previous
            );
        }

        return new ConfigurationContainer($data);
    }

    /**
     * @inheritDoc
     */
    public function supports(mixed $resource): bool
    {
        if (!Type\non_empty_string()->matches($resource) || !(Str\ends_with($resource, '.yaml') || Str\ends_with($resource, '.yml'))) {
            return false;
        }

        return Filesystem\is_file($resource) && Filesystem\is_readable($resource);
    }
}
