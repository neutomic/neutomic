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

namespace Neu\Component\DependencyInjection;

use Neu\Component\DependencyInjection\Exception\InvalidArgumentException;
use Psl\Env;
use Psl\Filesystem;
use Psl\Str;

final readonly class Project
{
    public const string MODE_ENVIRONMENT_VARIABLE = 'PROJECT_MODE';
    public const string DEBUG_ENVIRONMENT_VARIABLE = 'PROJECT_DEBUG';

    private const string DEFAULT_CONFIG = 'config';
    private const string DEFAULT_SOURCE = 'src';

    /**
     * The project mode.
     */
    public ProjectMode $mode;

    /**
     * The project debug mode.
     */
    public bool $debug;

    /**
     * The project name.
     *
     * @var non-empty-string
     */
    public string $name;

    /**
     * The project directory.
     *
     * @var non-empty-string
     */
    public string $directory;

    /**
     * The project source, typically the project directory with `src` appended.
     *
     * @var non-empty-string|null
     */
    public null|string $source;

    /**
     * The project configuration, typically the project directory with `config` appended.
     *
     * @var non-empty-string|null
     */
    public null|string $config;

    /**
     * The project entry point.
     *
     * @var non-empty-string
     */
    public string $entrypoint;

    /**
     * Create a new project instance.
     *
     * @param ProjectMode $mode The project mode.
     * @param bool $debug The project debug mode.
     * @param non-empty-string $name The project name.
     * @param non-empty-string $directory The project directory.
     * @param non-empty-string|null $source The project source.
     * @param non-empty-string|null $config The project configuration.
     * @param non-empty-string $entrypoint The project entry point.
     */
    private function __construct(ProjectMode $mode, bool $debug, string $name, string $directory, null|string $source, null|string $config, string $entrypoint)
    {
        $this->mode = $mode;
        $this->debug = $debug;
        $this->name = $name;
        $this->directory = $directory;
        $this->source = $source;
        $this->config = $config;
        $this->entrypoint = $entrypoint;
    }

    /**
     * Create a new project instance from a directory, and an entry point.
     *
     * This method will infer the project mode from the environment, and use the directory name as the project name.
     *
     * For source and configuration, it will use the default values: `src` and `config`.
     *
     * @param non-empty-string $directory The project directory.
     * @param non-empty-string $entrypoint The project entry point.
     *
     * @throws Exception\RuntimeException If the project directory does not exist.
     * @throws Exception\InvalidArgumentException If the project mode set in the environment is invalid.
     */
    public static function create(string $directory, string $entrypoint): self
    {
        $mode = self::getModeFromEnvironment();
        $debug = self::getDebugFromEnvironment();
        if (!Filesystem\is_directory($directory)) {
            throw new Exception\RuntimeException('The project directory does not exist.');
        }

        $directory = Filesystem\canonicalize($directory) ?? $directory;
        $name = Filesystem\get_basename($directory);

        $source = $directory . Filesystem\SEPARATOR . self::DEFAULT_SOURCE;
        $config = $directory . Filesystem\SEPARATOR . self::DEFAULT_CONFIG;

        return new self($mode, $debug, $name, $directory, $source, $config, $entrypoint);
    }

    /**
     * Retrieve the current project mode from the environment.
     *
     * If the environment variable is not set, the default project mode will be used.
     *
     * If no default project mode is provided, the default project mode will be {@see ProjectMode::Development}.
     *
     * @param ProjectMode|null $default The default project mode to use if the environment variable is not set.
     *
     * @throws InvalidArgumentException If the project mode set in the environment is invalid.
     *
     * @return ProjectMode The project mode.
     */
    public static function getModeFromEnvironment(null|ProjectMode $default = null): ProjectMode
    {
        /** @psalm-suppress MissingThrowsDocblock */
        $value = Env\get_var(self::MODE_ENVIRONMENT_VARIABLE);
        if (null === $value) {
            return $default ?? ProjectMode::Development;
        }

        return ProjectMode::fromString($value);
    }

    /**
     * Retrieve the current debug mode from the environment.
     *
     * If the environment variable is not set, the default debug mode will be used.
     *
     * If no default debug mode is provided, the default debug mode will be `false`.
     *
     * @param bool|null $default The default debug mode to use if the environment variable is not set.
     *
     * @throws InvalidArgumentException If the debug mode set in the environment is invalid.
     *
     * @return bool The debug mode.
     */
    public static function getDebugFromEnvironment(null|bool $default = null): bool
    {
        /** @psalm-suppress MissingThrowsDocblock */
        $value = Env\get_var(self::DEBUG_ENVIRONMENT_VARIABLE);
        if (null === $value) {
            return $default ?? false;
        }

        $value = Str\lowercase($value);

        return match ($value) {
            '1', 'true', 'on', 'yes' => true,
            '0', 'false', 'off', 'no' => false,
            default => throw new InvalidArgumentException('Invalid debug mode value: ' . $value),
        };
    }

    /**
     * Create a new project instance with a different mode.
     *
     * @param ProjectMode $mode The project mode.
     */
    public function withMode(ProjectMode $mode): self
    {
        return new self($mode, $this->debug, $this->name, $this->directory, $this->source, $this->config, $this->entrypoint);
    }

    /**
     * Create a new project instance with a different debug mode.
     *
     * @param bool $debug The project debug mode.
     */
    public function withDebug(bool $debug): self
    {
        return new self($this->mode, $debug, $this->name, $this->directory, $this->source, $this->config, $this->entrypoint);
    }

    /**
     * Create a new project instance with a different name.
     *
     * @param non-empty-string $name The project name.
     */
    public function withName(string $name): self
    {
        return new self($this->mode, $this->debug, $name, $this->directory, $this->source, $this->config, $this->entrypoint);
    }

    /**
     * Create a new project instance with a different directory.
     *
     * @param non-empty-string $directory The project directory.
     *
     * @throws Exception\RuntimeException If the project directory does not exist.
     */
    public function withDirectory(string $directory): self
    {
        if (!Filesystem\is_directory($directory)) {
            throw new Exception\RuntimeException('The project directory does not exist.');
        }

        $directory = Filesystem\canonicalize($directory) ?? $directory;

        return new self($this->mode, $this->debug, $this->name, $directory, $this->source, $this->config, $this->entrypoint);
    }

    /**
     * Create a new project instance with a different source.
     *
     * @param non-empty-string|null $source The project source.
     *
     * @throws Exception\RuntimeException If the project source does not exist.
     */
    public function withSource(null|string $source): self
    {
        if (null !== $source && !Filesystem\is_directory($source)) {
            throw new Exception\RuntimeException('The project source does not exist.');
        }

        if (null !== $source) {
            $source = Filesystem\canonicalize($source);
        }

        return new self($this->mode, $this->debug, $this->name, $this->directory, $source, $this->config, $this->entrypoint);
    }

    /**
     * Create a new project instance with a different configuration.
     *
     * @param non-empty-string|null $config The project configuration.
     *
     * @throws Exception\RuntimeException If the project configuration does not exist.
     */
    public function withConfig(null|string $config): self
    {
        if (null !== $config && !Filesystem\is_file($config) && !Filesystem\is_directory($config)) {
            throw new Exception\RuntimeException('The project configuration does not exist.');
        }

        if (null !== $config) {
            $config = Filesystem\canonicalize($config);
        }

        return new self($this->mode, $this->debug, $this->name, $this->directory, $this->source, $config, $this->entrypoint);
    }

    /**
     * Create a new project instance with a different entry point.
     *
     * @param non-empty-string $entrypoint The project entry point.
     */
    public function withEntrypoint(string $entrypoint): self
    {
        return new self($this->mode, $this->debug, $this->name, $this->directory, $this->source, $this->config, $entrypoint);
    }

    /**
     * Resolve a path relative to the project directory.
     *
     * @param non-empty-string $path The path to resolve.
     *
     * @return non-empty-string The resolved path.
     */
    public function resolve(string $path): string
    {
        // $path is absolute, return it as is
        if (Str\Byte\starts_with($path, '/')) {
            return $path;
        }

        $map = [
            '%project%' => $this->directory,
            '%entry%' => $this->entrypoint,
        ];

        if (null !== $this->source) {
            $map['%source%'] = $this->source;
        }

        if (null !== $this->config) {
            $map['%config%'] = $this->config;
        }

        $normalizedPath = Str\Byte\trim_left($path, '/');
        $result = $this->directory . '/' . $normalizedPath;
        foreach ($map as $prefix => $directory) {
            if (Str\Byte\starts_with($normalizedPath, $prefix)) {
                $result = $directory . '/' . Str\Byte\trim_left(Str\Byte\strip_prefix($normalizedPath, $prefix), '/');
                break;
            }
        }

        /** @var non-empty-string */
        return Str\Byte\replace($result, '%mode%', $this->mode->value);
    }
}
