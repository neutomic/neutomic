<?php

declare(strict_types=1);

namespace Neu\Component\Console;

/**
 * Configuration for the application.
 */
final readonly class Configuration
{
    /**
     * The name of the application.
     *
     * @var non-empty-string
     */
    public string $name;

    /**
     * The version of the application.
     */
    public string $version;

    /**
     * A decorator banner to `brand` the application.
     */
    public string $banner;

    /**
     * Whether the application should enable the help flag.
     *
     * @see Application::bootstrap()
     */
    public bool $helpFlagEnabled;

    /**
     * Whether the application should enable the quiet mode flag.
     *
     * @see Application::bootstrap()
     */
    public bool $quietFlagEnabled;

    /**
     * Whether the application should enable the verbose mode flag.
     *
     * @see Application::bootstrap()
     */
    public bool $verboseFlagEnabled;

    /**
     * Whether the application should enable the version flag.
     *
     * @see Application::bootstrap()
     */
    public bool $versionFlagEnabled;

    /**
     * Whether the application should enable the no-interaction flag.
     *
     * @see Application::bootstrap()
     */
    public bool $noInteractionFlagEnabled;

    /**
     * Whether the application should enable the ansi flag.
     *
     * @see Application::bootstrap()
     */
    public bool $ansiFlagEnabled;

    /**
     * Whether the application should enable the no-ansi flag.
     *
     * @see Application::bootstrap()
     */
    public bool $noAnsiFlagEnabled;

    /**
     * Create a new Configuration instance.
     *
     * @param non-empty-string $name
     */
    public function __construct(
        string $name,
        string $version = '',
        string $banner = '',
        bool $helpEnabled = true,
        bool $quietEnabled = true,
        bool $verboseEnabled = true,
        bool $versionEnabled = true,
        bool $noInteractionEnabled = true,
        bool $ansiEnabled = true,
        bool $noAnsiEnabled = true
    ) {
        $this->name = $name;
        $this->version = $version;
        $this->banner = $banner;
        $this->helpFlagEnabled = $helpEnabled;
        $this->quietFlagEnabled = $quietEnabled;
        $this->verboseFlagEnabled = $verboseEnabled;
        $this->versionFlagEnabled = $versionEnabled;
        $this->noInteractionFlagEnabled = $noInteractionEnabled;
        $this->ansiFlagEnabled = $ansiEnabled;
        $this->noAnsiFlagEnabled = $noAnsiEnabled;
    }

    /**
     * Create a new Configuration instance.
     *
     * @param non-empty-string $name
     */
    public static function create(string $name, string $version = '', string $banner = ''): self
    {
        return new self($name, $version, $banner);
    }

    /**
     * Return a new Configuration instance with the given name.
     *
     * @param non-empty-string $name
     */
    public function withName(string $name): self
    {
        return new self(
            $name,
            $this->version,
            $this->banner,
            $this->helpFlagEnabled,
            $this->quietFlagEnabled,
            $this->verboseFlagEnabled,
            $this->versionFlagEnabled,
            $this->noInteractionFlagEnabled,
            $this->ansiFlagEnabled,
            $this->noAnsiFlagEnabled,
        );
    }

    /**
     * Return a new Configuration instance with the given version.
     */
    public function withVersion(string $version): self
    {
        return new self(
            $this->name,
            $version,
            $this->banner,
            $this->helpFlagEnabled,
            $this->quietFlagEnabled,
            $this->verboseFlagEnabled,
            $this->versionFlagEnabled,
            $this->noInteractionFlagEnabled,
            $this->ansiFlagEnabled,
            $this->noAnsiFlagEnabled,
        );
    }

    /**
     * Return a new Configuration instance with the given banner.
     */
    public function withBanner(string $banner): self
    {
        return new self(
            $this->name,
            $this->version,
            $banner,
            $this->helpFlagEnabled,
            $this->quietFlagEnabled,
            $this->verboseFlagEnabled,
            $this->versionFlagEnabled,
            $this->noInteractionFlagEnabled,
            $this->ansiFlagEnabled,
            $this->noAnsiFlagEnabled,
        );
    }

    /**
     * Return a new Configuration instance with the given help flag enabled.
     *
     * @param bool $helpFlagEnabled
     */
    public function withHelpFlagEnabled(bool $helpFlagEnabled): self
    {
        return new self(
            $this->name,
            $this->version,
            $this->banner,
            $helpFlagEnabled,
            $this->quietFlagEnabled,
            $this->verboseFlagEnabled,
            $this->versionFlagEnabled,
            $this->noInteractionFlagEnabled,
            $this->ansiFlagEnabled,
            $this->noAnsiFlagEnabled,
        );
    }

    /**
     * Return a new Configuration instance with the given quiet flag enabled.
     *
     * @param bool $quietFlagEnabled
     */
    public function withQuietFlagEnabled(bool $quietFlagEnabled): self
    {
        return new self(
            $this->name,
            $this->version,
            $this->banner,
            $this->helpFlagEnabled,
            $quietFlagEnabled,
            $this->verboseFlagEnabled,
            $this->versionFlagEnabled,
            $this->noInteractionFlagEnabled,
            $this->ansiFlagEnabled,
            $this->noAnsiFlagEnabled,
        );
    }

    /**
     * Return a new Configuration instance with the given verbose flag enabled.
     *
     * @param bool $verboseFlagEnabled
     */
    public function withVerboseFlagEnabled(bool $verboseFlagEnabled): self
    {
        return new self(
            $this->name,
            $this->version,
            $this->banner,
            $this->helpFlagEnabled,
            $this->quietFlagEnabled,
            $verboseFlagEnabled,
            $this->versionFlagEnabled,
            $this->noInteractionFlagEnabled,
            $this->ansiFlagEnabled,
            $this->noAnsiFlagEnabled,
        );
    }

    /**
     * Return a new Configuration instance with the given version flag enabled.
     *
     * @param bool $versionFlagEnabled
     */
    public function withVersionFlagEnabled(bool $versionFlagEnabled): self
    {
        return new self(
            $this->name,
            $this->version,
            $this->banner,
            $this->helpFlagEnabled,
            $this->quietFlagEnabled,
            $this->verboseFlagEnabled,
            $versionFlagEnabled,
            $this->noInteractionFlagEnabled,
            $this->ansiFlagEnabled,
            $this->noAnsiFlagEnabled,
        );
    }

    /**
     * Return a new Configuration instance with the given no-interaction flag enabled.
     *
     * @param bool $noInteractionFlagEnabled
     */
    public function withNoInteractionFlagEnabled(bool $noInteractionFlagEnabled): self
    {
        return new self(
            $this->name,
            $this->version,
            $this->banner,
            $this->helpFlagEnabled,
            $this->quietFlagEnabled,
            $this->verboseFlagEnabled,
            $this->versionFlagEnabled,
            $noInteractionFlagEnabled,
            $this->ansiFlagEnabled,
            $this->noAnsiFlagEnabled,
        );
    }

    /**
     * Return a new Configuration instance with the given ansi flag enabled.
     *
     * @param bool $ansiFlagEnabled
     */
    public function withAnsiFlagEnabled(bool $ansiFlagEnabled): self
    {
        return new self(
            $this->name,
            $this->version,
            $this->banner,
            $this->helpFlagEnabled,
            $this->quietFlagEnabled,
            $this->verboseFlagEnabled,
            $this->versionFlagEnabled,
            $this->noInteractionFlagEnabled,
            $ansiFlagEnabled,
            $this->noAnsiFlagEnabled,
        );
    }

    /**
     * Return a new Configuration instance with the given no-ansi flag enabled.
     *
     * @param bool $noAnsiFlagEnabled
     */
    public function withNoAnsiFlagEnabled(bool $noAnsiFlagEnabled): self
    {
        return new self(
            $this->name,
            $this->version,
            $this->banner,
            $this->helpFlagEnabled,
            $this->quietFlagEnabled,
            $this->verboseFlagEnabled,
            $this->versionFlagEnabled,
            $this->noInteractionFlagEnabled,
            $this->ansiFlagEnabled,
            $noAnsiFlagEnabled,
        );
    }
}
