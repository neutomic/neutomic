<?php

declare(strict_types=1);

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
 */
final readonly class EnvironmentFactory implements FactoryInterface
{
    /**
     * Whether to enable debug mode.
     *
     * @var null|bool
     */
    private ?bool $debug;

    /**
     * The charset to use.
     *
     * @var null|string
     */
    private ?string $charset;

    /**
     * Whether to enable auto-reload.
     *
     * @var null|bool
     */
    private ?bool $autoReload;

    /**
     * Whether to enable strict variables.
     *
     * @var null|bool
     */
    private ?bool $strictVariables;

    /**
     * The auto-escape strategy to use.
     *
     * @var null|string
     */
    private ?string $autoEscape;

    /**
     * The optimizations strategy to use.
     *
     * @var null|int
     */
    private ?int $optimizations;

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
    public function __construct(?bool $debug = null, ?string $charset = null, ?bool $autoReload = null, ?bool $strictVariables = null, ?string $autoEscape = null, ?int $optimizations = null, ?array $globals = null)
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

        if (Environment::MAJOR_VERSION === 3)  {
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
