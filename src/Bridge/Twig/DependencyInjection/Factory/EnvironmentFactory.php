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

namespace Neu\Bridge\Twig\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Twig\Cache\CacheInterface;
use Twig\Cache\NullCache;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

/**
 * Factory for creating a {@see Environment} instance.
 *
 * @implements FactoryInterface<Environment>
 *
 * @psalm-suppress MixedAssignment
 */
final readonly class EnvironmentFactory implements FactoryInterface
{
    /**
     * Whether to enable debug mode.
     *
     * @var null|bool
     */
    private null|bool $debug;

    /**
     * The charset to use.
     *
     * @var null|string
     */
    private null|string $charset;

    /**
     * Whether to enable auto-reload.
     *
     * @var null|bool
     */
    private null|bool $autoReload;

    /**
     * Whether to enable strict variables.
     *
     * @var null|bool
     */
    private null|bool $strictVariables;

    /**
     * The auto-escape strategy to use.
     *
     * @var null|string
     */
    private null|string $autoEscape;

    /**
     * The optimizations strategy to use.
     *
     * @var null|int
     */
    private null|int $optimizations;

    /**
     * The global variables to pass to the template.
     *
     * @var array<string, mixed>
     */
    private array $globals;

    /**
     * @param null|bool $debug
     * @param null|string $charset
     * @param null|bool $autoReload
     * @param null|bool $strictVariables
     * @param null|non-empty-string $autoEscape
     * @param null|int $optimizations
     * @param array<string, mixed> $globals
     */
    public function __construct(null|bool $debug = null, null|string $charset = null, null|bool $autoReload = null, null|bool $strictVariables = null, null|string $autoEscape = null, null|int $optimizations = null, null|array $globals = null)
    {
        $this->debug = $debug;
        $this->charset = $charset;
        $this->autoReload = $autoReload;
        $this->strictVariables = $strictVariables;
        $this->autoEscape = $autoEscape;
        $this->optimizations = $optimizations;
        $this->globals = $globals ?? [];
    }

    public function __invoke(ContainerInterface $container): object
    {
        $options = [
            'debug' => $this->debug ?? $container->getProject()->debug,
        ];

        if (null !== $this->charset) {
            $options['charset'] = $this->charset;
        }

        if (null !== $this->autoEscape) {
            $options['autoescape'] = $this->autoEscape;
        }

        if (null !== $this->autoReload) {
            $options['auto_reload'] = $this->autoReload;
        } else {
            // If auto-reload is not explicitly set, use the project mode to determine the value.
            $options['auto_reload'] = $container->getProject()->mode->isDevelopment();
        }

        if (null !== $this->optimizations) {
            $options['optimizations'] = $this->optimizations;
        }

        if (null !== $this->strictVariables) {
            $options['strict_variables'] = $this->strictVariables;
        }

        if (Environment::MAJOR_VERSION === 3) {
            $options['use_yield'] = true;
        }

        $loader = $container->getTyped(LoaderInterface::class, LoaderInterface::class);

        $environment = new Environment($loader, $options);

        foreach ($this->globals as $name => $value) {
            $environment->addGlobal($name, $value);
        }

        if ($container->has(CacheInterface::class)) {
            $cache = $container->getTyped(CacheInterface::class, CacheInterface::class);

            $environment->setCache($cache);
        } else {
            $environment->setCache(new NullCache());
        }

        return $environment;
    }
}
