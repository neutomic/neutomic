<?php

declare(strict_types=1);

namespace Neu\Component\Configuration\Loader;

use Neu\Component\Configuration\ConfigurationContainer;
use Neu\Component\Configuration\ConfigurationContainerInterface;
use Neu\Component\Configuration\Exception\InvalidConfigurationException;
use Psl\Filesystem;
use Psl\Str;
use Psl\Type;

/**
 * @implements LoaderInterface<non-empty-string>
 */
final class PHPFileLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     */
    public function load(mixed $resource): ConfigurationContainerInterface
    {
        /** @var array<string, mixed>|mixed $data */
        $data = (static function () use ($resource): mixed {
            /** @psalm-suppress UnresolvableInclude */
            return @require $resource;
        })();


        try {
            $data = Type\dict(Type\string(), Type\mixed())->coerce($data);
        } catch (Type\Exception\CoercionException $previous) {
            throw new InvalidConfigurationException(
                'Failed to coerce php resource file "' . $resource . '".',
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
        if (!Type\non_empty_string()->matches($resource) || !Str\ends_with($resource, '.php')) {
            return false;
        }

        return Filesystem\is_file($resource) && Filesystem\is_readable($resource);
    }
}
