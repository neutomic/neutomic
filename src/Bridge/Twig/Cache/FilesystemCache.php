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

namespace Neu\Bridge\Twig\Cache;

use Amp\File;
use Twig\Cache\FilesystemCache as TwigFilesystemCache;

use function dirname;
use function function_exists;
use function ini_get;

use const FILTER_VALIDATE_BOOLEAN;

/**
 * An asynchronous filesystem cache for compiled Twig templates.
 *
 * @psalm-suppress UnresolvableInclude
 */
final class FilesystemCache extends TwigFilesystemCache
{
    private bool $shouldForceBytecodeInvalidation;
    private bool $canInvalidate;

    public function __construct(string $directory, int $options = 0)
    {
        parent::__construct($directory, $options);

        $this->shouldForceBytecodeInvalidation = self::FORCE_BYTECODE_INVALIDATION === ($options & self::FORCE_BYTECODE_INVALIDATION);
        $this->canInvalidate = function_exists('opcache_invalidate') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @inheritDoc
     */
    public function load(string $key): void
    {
        if (File\exists($key)) {
            @include_once $key;
        }
    }

    public function write(string $key, string $content): void
    {
        $dir = dirname($key);
        if (!File\isDirectory($dir)) {
            File\createDirectoryRecursively($dir);
        }

        File\write($key, $content);
        File\changePermissions($key, 0666 & ~umask());

        if ($this->shouldForceBytecodeInvalidation && $this->canInvalidate) {
            @opcache_invalidate($key, true);
        }
    }

    /**
     * @inheritDoc
     */
    public function getTimestamp(string $key): int
    {
        if (!File\isFile($key)) {
            return 0;
        }

        return File\getModificationTime($key);
    }
}
