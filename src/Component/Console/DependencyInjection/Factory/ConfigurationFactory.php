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

namespace Neu\Component\Console\DependencyInjection\Factory;

use Neu\Component\Console\Configuration;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * A factory for creating a new instance of the {@see Configuration}.
 *
 * @implements FactoryInterface<Configuration>
 */
final readonly class ConfigurationFactory implements FactoryInterface
{
    /**
     * The name of the application.
     *
     * @var non-empty-string
     */
    private null|string $name;

    /**
     * The version of the application.
     */
    private null|string $version;

    /**
     * A decorator banner to "brand" the application.
     */
    private null|string $banner;

    /**
     * Whether the application should enable the help flag.
     */
    private null|bool $helpFlag;

    /**
     * Whether the application should enable the quiet mode flag.
     */
    private null|bool $quietFlag;

    /**
     * Whether the application should enable the verbose mode flag.
     */
    private null|bool $verboseFlag;

    /**
     * Whether the application should enable the version flag.
     */
    private null|bool $versionFlag;

    /**
     * Whether the application should enable the ansi flag.
     */
    private null|bool $ansiFlag;

    /**
     * Whether the application should enable the no-ansi flag.
     */
    private null|bool $noAnsiFlag;

    /**
     * Whether the application should enable the no-interaction flag.
     */
    private null|bool $noInteractionFlag;

    /**
     * Creates a new {@see ConfigurationFactory} instance.
     *
     * @param ?non-empty-string $name The name of the application.
     * @param ?string $version The version of the application.
     * @param ?string $banner A decorator banner to "brand" the application.
     * @param ?bool $helpFlag Whether the application should enable the help flag.
     * @param ?bool $quietFlag Whether the application should enable the quiet mode flag.
     * @param ?bool $verboseFlag Whether the application should enable the verbose mode flag.
     * @param ?bool $versionFlag Whether the application should enable the version flag.
     * @param ?bool $ansiFlag Whether the application should enable the ansi flag.
     * @param ?bool $noAnsiFlag Whether the application should enable the no-ansi flag.
     * @param ?bool $noInteractionFlag Whether the application should enable the no-interaction flag.
     */
    public function __construct(
        null|string $name = null,
        null|string $version = null,
        null|string $banner = null,
        null|bool $helpFlag = null,
        null|bool $quietFlag = null,
        null|bool $verboseFlag = null,
        null|bool $versionFlag = null,
        null|bool $ansiFlag = null,
        null|bool $noAnsiFlag = null,
        null|bool $noInteractionFlag = null,
    ) {
        $this->name = $name;
        $this->version = $version;
        $this->banner = $banner;
        $this->helpFlag = $helpFlag;
        $this->quietFlag = $quietFlag;
        $this->verboseFlag = $verboseFlag;
        $this->versionFlag = $versionFlag;
        $this->ansiFlag = $ansiFlag;
        $this->noAnsiFlag = $noAnsiFlag;
        $this->noInteractionFlag = $noInteractionFlag;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): object
    {
        $name = $this->name ?? $container->getProject()->name;

        $configuration = new Configuration($name);

        if ($this->version !== null) {
            $configuration = $configuration->withVersion($this->version);
        }

        if ($this->banner !== null) {
            $configuration = $configuration->withBanner($this->banner);
        }

        if ($this->helpFlag !== null) {
            $configuration = $configuration->withHelpFlagEnabled($this->helpFlag);
        }

        if ($this->quietFlag !== null) {
            $configuration = $configuration->withQuietFlagEnabled($this->quietFlag);
        }

        if ($this->verboseFlag !== null) {
            $configuration = $configuration->withVerboseFlagEnabled($this->verboseFlag);
        }

        if ($this->versionFlag !== null) {
            $configuration = $configuration->withVersionFlagEnabled($this->versionFlag);
        }

        if ($this->ansiFlag !== null) {
            $configuration = $configuration->withAnsiFlagEnabled($this->ansiFlag);
        }

        if ($this->noAnsiFlag !== null) {
            $configuration = $configuration->withNoAnsiFlagEnabled($this->noAnsiFlag);
        }

        if ($this->noInteractionFlag !== null) {
            $configuration = $configuration->withNoInteractionFlagEnabled($this->noInteractionFlag);
        }

        return $configuration;
    }
}
