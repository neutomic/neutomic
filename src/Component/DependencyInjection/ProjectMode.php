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

/**
 * Project mode enumeration, used to determine the current project mode.
 */
enum ProjectMode: string
{
    public const string ENVIRONMENT_VARIABLE = 'PROJECT_MODE';

    case Development = 'development';
    case Production = 'production';
    case Testing = 'testing';

    /**
     * Retrieve the current project mode from the environment.
     *
     * If the environment variable is not set, the default project mode will be used.
     * If no default project mode is provided, the default project mode will be {@see ProjectMode::Development}.
     *
     * @param ProjectMode|null $default The default project mode to use if the environment variable is not set.
     *
     * @throws InvalidArgumentException If the project mode set in the environment is invalid.
     */
    public static function fromEnvironment(null|ProjectMode $default = null): self
    {
        /** @psalm-suppress MissingThrowsDocblock */
        $value = Env\get_var(self::ENVIRONMENT_VARIABLE);
        if (null === $value) {
            return $default ?? self::Development;
        }

        return self::fromString($value);
    }

    /**
     * Retrieve the current project mode from a string.
     *
     * @throws InvalidArgumentException If the provided mode is invalid.
     */
    public static function fromString(string $mode): self
    {
        return match ($mode) {
            'development', 'dev', 'd' => self::Development,
            'production', 'prod', 'p' => self::Production,
            'testing', 'test', 't' => self::Testing,
            default => throw new InvalidArgumentException(
                'Invalid project mode "' . $mode . '", expected one of "development", "production", or "testing".',
            ),
        };
    }

    /**
     * Determine if the current project mode is development.
     */
    public function isDevelopment(): bool
    {
        return $this === self::Development;
    }

    /**
     * Determine if the current project mode is production.
     */
    public function isProduction(): bool
    {
        return $this === self::Production;
    }

    /**
     * Determine if the current project mode is testing.
     */
    public function isTesting(): bool
    {
        return $this === self::Testing;
    }

    /**
     * Get the possible values for the project mode.
     *
     * @return array<non-empty-string> The possible values for the project mode.
     */
    public function getPossibleValues(): array
    {
        return match ($this) {
            self::Development => ['development', 'dev', 'd'],
            self::Production => ['production', 'prod', 'p'],
            self::Testing => ['testing', 'test', 't'],
        };
    }
}
