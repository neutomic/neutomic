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

namespace Neu\Component\Advisory\Adviser;

use Neu\Component\Advisory\Advice;
use Psl\OS;
use Psl\Runtime;
use Psl\Str;

use function extension_loaded;
use function ini_get;

/**
 * Adviser that provides comprehensive advice on OPCache configuration for better performance.
 */
final readonly class OPCacheAdviser implements AdviserInterface
{
    private const int OPCACHE_MEMORY_THRESHOLD = 128;
    private const int MAX_ACCELERATED_FILES_THRESHOLD = 6000;
    private const int INTERNED_STRINGS_BUFFER_THRESHOLD = 16;
    private const int JIT_BUFFER_SIZE_THRESHOLD = 256;

    /**
     * Retrieve an advice instance regarding OPCache configuration.
     *
     * @return Advice|null An instance of Advice if OPCache configuration is not optimal, or null if it is set correctly.
     */
    public function getAdvice(): null|Advice
    {
        if (!$this->isOpcacheInstalled()) {
            return Advice::forPerformance(
                'Install ext-opcache',
                'The ext-opcache extension is not installed. Installing it can significantly improve PHP performance by caching precompiled script bytecode in shared memory.',
                'Install the ext-opcache extension and ensure it is enabled in your PHP configuration.'
            );
        }

        if (!$this->isOpcacheEnabled()) {
            return Advice::forPerformance(
                'Enable OPCache',
                'OPCache should be enabled in production environments to improve PHP performance.',
                'Enable OPCache in the PHP configuration file by setting the `opcache.enable` directive to `1`.'
            );
        }

        if (!$this->isOpcacheEnabledForCli()) {
            return Advice::forPerformance(
                'Enable OPCache for CLI',
                'OPCache should be enabled for CLI in production environments to improve PHP performance.',
                'Enable OPCache for CLI in the PHP configuration file by setting the `opcache.enable_cli` directive to `1`.'
            );
        }

        if (!$this->isOpcacheMemorySufficient()) {
            return Advice::forPerformance(
                'Increase OPCache Memory Consumption',
                'The current OPCache memory consumption setting is too low, which can lead to performance issues.',
                'Increase the opcache.memory_consumption setting in your php.ini configuration file to at least 128MB.'
            );
        }

        if (!$this->isMaxAcceleratedFilesSufficient()) {
            return Advice::forPerformance(
                'Increase OPCache Max Accelerated Files',
                'The current opcache.max_accelerated_files setting is too low. This can limit the number of PHP files that can be cached, affecting performance.',
                'Increase the opcache.max_accelerated_files setting in your php.ini configuration file to at least 6,000.'
            );
        }

        if (!$this->isInternedStringsBufferSufficient()) {
            return Advice::forPerformance(
                'Increase OPCache Interned Strings Buffer',
                'The current opcache.interned_strings_buffer setting is too low, which can affect performance.',
                'Increase the opcache.interned_strings_buffer setting in your php.ini configuration file to at least 16MB.'
            );
        }

        if (!$this->isJitSupported()) {
            return Advice::forPerformance(
                'OPCache JIT Not Supported',
                'OPCache JIT is not supported on Apple Silicon when PHP is built with ZTS enabled.',
                'Use a non-Zend Thread Safety (ZTS) build of PHP to enable OPCache JIT on Apple Silicon.'
            );
        }

        if ($this->isJitDisabled()) {
            return Advice::forPerformance(
                'Enable OPCache JIT',
                'The current opcache.jit setting is disabled. Enabling JIT can improve performance significantly.',
                'Enable and configure JIT in your php.ini configuration file. Recommended settings: opcache.jit=1255 and opcache.jit_buffer_size=256M or higher.'
            );
        }

        if (!$this->isJitBufferSizeSufficient()) {
            return Advice::forPerformance(
                'Increase OPCache JIT Buffer Size',
                'The current opcache.jit_buffer_size setting is too low, which can affect performance.',
                'Increase the opcache.jit_buffer_size setting in your php.ini configuration file to at least 256MB.'
            );
        }

        return null;
    }

    /**
     * Check if OPCache extension is installed.
     *
     * @return bool True if OPCache extension is installed, false otherwise.
     */
    private function isOpcacheInstalled(): bool
    {
        return extension_loaded('Zend OPcache');
    }

    /**
     * Check if OPCache is enabled.
     *
     * @return bool True if OPCache is enabled, false otherwise.
     */
    private function isOpcacheEnabled(): bool
    {
        return ini_get('opcache.enable') === '1';
    }

    /**
     * Check if OPCache is enabled for CLI.
     *
     * @return bool True if OPCache is enabled for CLI, false otherwise.
     */
    private function isOpcacheEnabledForCli(): bool
    {
        return ini_get('opcache.enable_cli') === '1';
    }

    /**
     * Check if OPCache memory consumption is sufficient.
     *
     * @return bool True if OPCache memory consumption is sufficient, false otherwise.
     */
    private function isOpcacheMemorySufficient(): bool
    {
        $opcacheMemory = (string) ini_get('opcache.memory_consumption');
        $opcacheMemory = Str\to_int($opcacheMemory);

        return $opcacheMemory !== null && $opcacheMemory >= self::OPCACHE_MEMORY_THRESHOLD;
    }


    /**
     * Check if OPCache max accelerated files is sufficient.
     *
     * @return bool True if OPCache max accelerated files is sufficient, false otherwise.
     */
    private function isMaxAcceleratedFilesSufficient(): bool
    {
        $files = (string) ini_get('opcache.max_accelerated_files');
        $files = Str\to_int($files);

        return $files !== null && $files >= self::MAX_ACCELERATED_FILES_THRESHOLD;
    }

    /**
     * Check if OPCache interned strings buffer is sufficient.
     *
     * @return bool True if OPCache interned strings buffer is sufficient, false otherwise.
     */
    private function isInternedStringsBufferSufficient(): bool
    {
        $buffer = (string) ini_get('opcache.interned_strings_buffer');
        $buffer = Str\to_int($buffer);

        return $buffer !== null && $buffer >= self::INTERNED_STRINGS_BUFFER_THRESHOLD;
    }

    /**
     * Check if JIT is supported.
     *
     * @return bool True if JIT is supported, false otherwise.
     */
    private function isJitSupported(): bool
    {
        return !OS\is_darwin() || !Runtime\is_thread_safe();
    }

    /**
     * Check if OPCache JIT is disabled.
     *
     * @return bool True if OPCache JIT is disabled, false otherwise.
     */
    private function isJitDisabled(): bool
    {
        $jit = ini_get('opcache.jit');

        return $jit === false || $jit === 'disable' || $jit === 'off';
    }

    /**
     * Check if OPCache JIT buffer size is sufficient.
     *
     * @return bool True if OPCache JIT buffer size is sufficient, false otherwise.
     */
    private function isJitBufferSizeSufficient(): bool
    {
        $size = (string) ini_get('opcache.jit_buffer_size');
        $size = Internal\Utility::parseValue($size);

        return $size >= self::JIT_BUFFER_SIZE_THRESHOLD;
    }
}
