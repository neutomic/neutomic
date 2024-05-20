<?php

declare(strict_types=1);

namespace Neu\Component\Console;

use Neu\Component\Console\Input\HandleInput;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\HandleConsoleOutput;
use Neu\Component\Console\Output\HandleOutput;
use Neu\Component\Console\Output\OutputInterface;
use Psl\Dict;
use Psl\Env;
use Psl\IO;
use Psl\OS;
use Psl\Regex;
use Psl\Shell;
use Psl\Str;
use Psl\Vec;

use function function_exists;
use function sapi_windows_vt100_support;
use function stream_isatty;

final class Terminal
{
    private const int DEFAULT_HEIGHT = 24;
    private const int DEFAULT_WIDTH = 80;

    private static ?int $height = null;
    private static ?int $width = null;

    private static ?bool $colorSupport = null;

    private static ?bool $interactive = null;

    /**
     * Returns the default terminal input.
     *
     * @internal
     */
    public static function getInput(): InputInterface
    {
        // IN via Psl\IO
        return new HandleInput(IO\input_handle(), Vec\values(Dict\drop(Env\args(), 1)));
    }

    /**
     * Returns the default terminal output.
     *
     * @internal
     */
    public static function getOutput(): OutputInterface
    {
        $standardOutputHandle = IO\output_handle();
        $standardErrorOutputHandle = IO\error_handle();
        if (null === $standardErrorOutputHandle) {
            return new HandleOutput($standardOutputHandle);
        }

        return new HandleConsoleOutput(
            $standardOutputHandle,
            $standardErrorOutputHandle
        );
    }

    /**
     * Set the terminal height.
     */
    public static function setHeight(int $height): void
    {
        self::$height = $height;
    }

    /**
     * Get the terminal height.
     */
    public static function getHeight(): int
    {
        $lines = Env\get_var('LINES');
        if ($lines !== null) {
            $lines = Str\to_int($lines);
            if ($lines !== null) {
                return $lines;
            }
        }

        if (self::$height !== null) {
            return self::$height;
        }

        if (OS\is_windows()) {
            return self::$height = self::DEFAULT_HEIGHT;
        }

        $dimensions = self::getDimensionsUsingStty();
        self::$height = $dimensions['height'] ?? self::DEFAULT_HEIGHT;
        self::$width ??= $dimensions['width'] ?? self::DEFAULT_WIDTH;

        return self::$height;
    }

    /**
     * Set the terminal width.
     */
    public static function setWidth(int $width): void
    {
        self::$width = $width;
    }

    /**
     * Get the terminal width.
     */
    public static function getWidth(): int
    {
        $cols = Env\get_var('COLUMNS');
        if ($cols !== null) {
            $cols = Str\to_int($cols);
            if ($cols !== null) {
                return $cols;
            }
        }

        if (self::$width !== null) {
            return self::$width;
        }

        if (OS\is_windows()) {
            return self::$width = self::DEFAULT_WIDTH;
        }

        $dimensions = self::getDimensionsUsingStty();
        self::$width = $dimensions['width'] ?? self::DEFAULT_WIDTH;
        self::$height ??= $dimensions['height'] ?? self::DEFAULT_HEIGHT;

        return self::$width;
    }

    /**
     * Set the terminal color support.
     */
    public static function setColorSupport(bool $colorSupport): void
    {
        self::$colorSupport = $colorSupport;
    }

    /**
     * Check if the terminal supports color.
     */
    public static function hasColorSupport(): bool
    {
        if (self::$colorSupport !== null) {
            return self::$colorSupport;
        }

        // Follow https://no-color.org/
        if (Env\get_var('NO_COLOR') !== null) {
            return self::$colorSupport = false;
        }

        $colors = Env\get_var('CLICOLORS');
        if ($colors !== null) {
            if ($colors === '1' || $colors === 'yes' || $colors === 'true' || $colors === 'on') {
                return self::$colorSupport = true;
            }

            if ($colors === '0' || $colors === 'no' || $colors === 'false' || $colors === 'off') {
                return self::$colorSupport = false;
            }
        }

        if (Env\get_var('TRAVIS') !== null) {
            return  self::$colorSupport = true;
        }

        if (Env\get_var('CIRCLECI') !== null) {
            return  self::$colorSupport = true;
        }

        if (Env\get_var('TERM') === 'xterm') {
            return  self::$colorSupport = true;
        }

        if (Env\get_var('TERM_PROGRAM') === 'Hyper') {
            return  self::$colorSupport = true;
        }

        if (OS\is_windows()) {
            if (Env\get_var('ANSICON') !== '') {
                return self::$colorSupport = true;
            }

            if (Env\get_var('ConEmuANSI') === 'ON') {
                return self::$colorSupport = true;
            }
        }

        $stream = IO\output_handle()->getStream();
        if (OS\is_windows() && function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support($stream)) {
            return self::$colorSupport = true;
        }

        if (function_exists('posix_isatty') && @posix_isatty($stream)) {
            return self::$colorSupport = true;
        }

        if (@stream_isatty($stream)) {
            return self::$colorSupport = true;
        }

        // Default
        return self::$colorSupport = false;
    }

    /**
     * Set the terminal interactive mode.
     */
    public static function setInteractive(bool $interactive): void
    {
        self::$interactive = $interactive;
    }

    /**
     * Check if the terminal is interactive.
     */
    public static function isInteractive(): bool
    {
        if (self::$interactive !== null) {
            return self::$interactive;
        }

        $noninteractive = Env\get_var('NONINTERACTIVE');
        if ($noninteractive !== null) {
            if ($noninteractive === '1' || $noninteractive === 'true' || $noninteractive === 'yes') {
                return self::$interactive = false;
            }

            if ($noninteractive === '0' || $noninteractive === 'false' || $noninteractive === 'no') {
                return self::$interactive = true;
            }
        }

        // Detects TravisCI and CircleCI; Travis gives you a TTY for STDIN
        $ci = Env\get_var('CI');
        if ($ci === '1' || $ci === 'true') {
            return self::$interactive = false;
        }

        $stream = IO\input_handle()->getStream();

        if (OS\is_windows() && function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support($stream)) {
            return self::$interactive = true;
        }

        if (function_exists('posix_isatty') &&  @posix_isatty($stream)) {
            return self::$interactive = true;
        }

        if (@stream_isatty($stream)) {
            return self::$interactive = true;
        }

        return self::$interactive = false;
    }

    /**
     * Initializes dimensions using the output of a stty columns line.
     *
     * @return array{width: ?int, height: ?int}
     */
    private static function getDimensionsUsingStty(): array
    {
        try {
            $sttyString = Shell\execute('stty -a | grep columns');

            if ($matches = Regex\first_match($sttyString, "/rows.(\d+);.columns.(\d+);/i")) {
                // extract [w, h] from "rows h; columns w;"
                return [
                    'width' => Str\to_int($matches[2]),
                    'height' => Str\to_int($matches[1]),
                ];
            }

            if ($matches = Regex\first_match($sttyString, "/;.(\d+).rows;.(\d+).columns/i")) {
                // extract [w, h] from "; h rows; w columns"
                return [
                    'width' => Str\to_int($matches[2]),
                    'height' => Str\to_int($matches[1]),
                ];
            }

            return ['width' => null, 'height' => null];
        } catch (Shell\Exception\FailedExecutionException) {
            return ['width' => null, 'height' => null];
        }
    }
}
