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

namespace Neu\Bridge\Monolog\DependencyInjection;

use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\ScalarFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\Processor\ClosureContextProcessor;
use Monolog\Processor\HostnameProcessor;
use Monolog\Processor\LoadAverageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Formatter\ConsoleFormatterFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Formatter\HtmlFormatterFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Formatter\JsonFormatterFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Formatter\LineFormatterFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Formatter\NormalizerFormatterFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Formatter\ScalarFormatterFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Handler\FileHandlerFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Handler\NullHandlerFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Handler\StderrHandlerFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Handler\StdoutHandlerFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\LoggerFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Processor\ClosureContextProcessorFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Processor\HostnameProcessorFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Processor\LoadAverageProcessorFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Processor\MemoryPeakUsageProcessorFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Processor\MemoryUsageProcessorFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Processor\ProcessIdProcessorFactory;
use Neu\Bridge\Monolog\DependencyInjection\Factory\Processor\PsrLogMessageProcessorFactory;
use Neu\Bridge\Monolog\DependencyInjection\Processor\LoggerAwareProcessor;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Psl\Type;
use Psr\Log\LoggerInterface;

use function array_key_exists;
use function array_key_first;

/**
 * A container extension for the Monolog library.
 *
 * @psalm-type LoggerConfiguration = array{
 *     handlers?: list<non-empty-string>,
 *     processors?: list<non-empty-string>,
 *     timezone?: non-empty-string,
 *     use-logging-loop-detection?: bool,
 *     use-microsecond-timestamps?: bool,
 * }
 * @psalm-type NullHandlerConfiguration = array{
 *     type: 'null',
 *     level?: string|int
 * }
 * @psalm-type StderrHandlerConfiguration = array{
 *     type: 'stderr',
 *     level?: string|int,
 *     bubble?: bool,
 *     formatter?: non-empty-string,
 *     processors?: list<non-empty-string>,
 * }
 * @psalm-type StdoutHandlerConfiguration = array{
 *     type: 'stdout',
 *     level?: string|int,
 *     bubble?: bool,
 *     formatter?: non-empty-string,
 *     processors?: list<non-empty-string>,
 * }
 * @psalm-type FileHandlerConfiguration = array{
 *     type: 'file',
 *     file: non-empty-string,
 *     level?: string|int,
 *     bubble?: bool,
 *     formatter?: non-empty-string,
 *     processors?: list<non-empty-string>,
 * }
 * @psalm-type HandlerConfiguration = NullHandlerConfiguration|StderrHandlerConfiguration|StdoutHandlerConfiguration|FileHandlerConfiguration
 * @psalm-type ConsoleFormatterConfiguration = array{
 *     format?: string,
 *     date-format?: string,
 *     allow-inline-line-breaks?: bool,
 *     ignore-empty-context-and-extra?: bool,
 *     include-stack-traces?: bool
 * }
 * @psalm-type HtmlFormatterConfiguration = array{
 *     date-format?: string
 * }
 * @psalm-type JsonFormatterConfiguration = array{
 *     batch-mode?: 1|2,
 *     append-newline?: bool,
 *     ignore-empty-context-and-extra?: bool,
 *     include-stack-traces?: bool
 * }
 * @psalm-type LineFormatterConfiguration = array{
 *     format?: string,
 *     date-format?: string,
 *     allow-inline-line-breaks?: bool,
 *     ignore-empty-context-and-extra?: bool,
 *     include-stack-traces?: bool
 * }
 * @psalm-type NormalizerFormatterConfiguration = array{
 *     date-format?: string
 * }
 * @psalm-type ScalarFormatterConfiguration = array{
 *     date-format?: string
 * }
 * @psalm-type Configuration = array{
 *     default?: non-empty-string,
 *     channels?: array<non-empty-string, LoggerConfiguration>,
 *     handlers?: array<non-empty-string, HandlerConfiguration>,
 *     formatters?: array{
 *         console?: ConsoleFormatterConfiguration,
 *         html?: HtmlFormatterConfiguration,
 *         json?: JsonFormatterConfiguration,
 *         line?: LineFormatterConfiguration,
 *         normalizer?: NormalizerFormatterConfiguration,
 *         scalar?: ScalarFormatterConfiguration,
 *     },
 *     processors?: array{
 *         logger-aware-processor?: array{
 *             logger?: non-empty-string
 *         }
 *     }
 * }
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class MonologExtension implements ExtensionInterface
{
    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'default' => Type\optional(Type\non_empty_string()),
            'channels' => Type\optional(Type\dict(
                Type\non_empty_string(),
                Type\shape([
                    'handlers' => Type\optional(Type\vec(Type\non_empty_string())),
                    'processors' => Type\optional(Type\vec(Type\non_empty_string())),
                    'timezone' => Type\optional(Type\non_empty_string()),
                    'use-logging-loop-detection' => Type\optional(Type\bool()),
                    'use-microsecond-timestamps' => Type\optional(Type\bool()),
                ]),
            )),
            'handlers' => Type\optional(Type\dict(
                Type\non_empty_string(),
                Type\union(
                    Type\shape([
                        'type' => Type\literal_scalar('null'),
                        'level' => Type\optional(Type\union(Type\int(), Type\string())),
                    ]),
                    Type\shape([
                        'type' => Type\literal_scalar('stdout'),
                        'level' => Type\optional(Type\union(Type\int(), Type\string())),
                        'bubble' => Type\optional(Type\bool()),
                        'formatter' => Type\optional(Type\non_empty_string()),
                        'processors' => Type\optional(Type\vec(Type\non_empty_string())),
                    ]),
                    Type\shape([
                        'type' => Type\literal_scalar('stderr'),
                        'level' => Type\optional(Type\union(Type\int(), Type\string())),
                        'bubble' => Type\optional(Type\bool()),
                        'formatter' => Type\optional(Type\non_empty_string()),
                        'processors' => Type\optional(Type\vec(Type\non_empty_string())),
                    ]),
                    Type\shape([
                        'type' => Type\literal_scalar('file'),
                        'file' => Type\non_empty_string(),
                        'level' => Type\optional(Type\union(Type\int(), Type\string())),
                        'bubble' => Type\optional(Type\bool()),
                        'formatter' => Type\optional(Type\non_empty_string()),
                        'processors' => Type\optional(Type\vec(Type\non_empty_string())),
                    ]),
                ),
            )),
            'formatters' => Type\optional(Type\shape([
                'console' => Type\optional(Type\shape([
                    'format' => Type\optional(Type\non_empty_string()),
                    'date-format' => Type\optional(Type\non_empty_string()),
                    'allow-inline-line-breaks' => Type\optional(Type\bool()),
                    'ignore-empty-context-and-extra' => Type\optional(Type\bool()),
                    'include-stack-traces' => Type\optional(Type\bool()),
                ])),
                'html' => Type\optional(Type\shape([
                    'date-format' => Type\optional(Type\non_empty_string()),
                ])),
                'json' => Type\optional(Type\shape([
                    'batch-mode' => Type\optional(Type\union(Type\literal_scalar(1), Type\literal_scalar(2))),
                    'append-newline' => Type\optional(Type\bool()),
                    'ignore-empty-context-and-extra' => Type\optional(Type\bool()),
                    'include-stack-traces' => Type\optional(Type\bool()),
                ])),
                'line' => Type\optional(Type\shape([
                    'format' => Type\optional(Type\non_empty_string()),
                    'date-format' => Type\optional(Type\non_empty_string()),
                    'allow-inline-line-breaks' => Type\optional(Type\bool()),
                    'ignore-empty-context-and-extra' => Type\optional(Type\bool()),
                    'include-stack-traces' => Type\optional(Type\bool()),
                ])),
                'normalizer' => Type\optional(Type\shape([
                    'date-format' => Type\optional(Type\non_empty_string()),
                ])),
                'scalar' => Type\optional(Type\shape([
                    'date-format' => Type\optional(Type\non_empty_string()),
                ])),
            ])),
            'processors' => Type\optional(Type\shape([
                'logger-aware-processor' => Type\optional(Type\shape([
                    'logger' => Type\optional(Type\non_empty_string()),
                ])),
            ])),
        ]);
    }

    public function register(ContainerBuilderInterface $container): void
    {
        $configuration = $container
            ->getConfiguration()
            ->getOfTypeOrDefault('monolog', $this->getConfigurationType(), [])
        ;

        $this->registerProcessors($container);
        $this->registerFormatters($container, $configuration);
        $this->registerHandlers($container, $configuration);
        $registeredChannels = $this->registerChannels($container, $configuration);

        if (array_key_exists('default', $configuration)) {
            $defaultLogger = $configuration['default'];
            if (!$container->hasDefinition($defaultLogger)) {
                $defaultLogger = 'monolog.logger.' . $defaultLogger;
            }
        } else {
            $defaultLogger = 'monolog.logger.' . array_key_first($registeredChannels);
        }

        $defaultLogger = $container->getDefinition($defaultLogger);
        $defaultLogger->addAlias(LoggerInterface::class);
        $defaultLogger->addAlias(Logger::class);

        $container->addProcessor(new LoggerAwareProcessor(
            $configuration['processors']['logger-aware-processor']['logger'] ?? null,
        ));
    }

    /**
     * @param ContainerBuilderInterface $container
     */
    private function registerProcessors(ContainerBuilderInterface $container): void
    {
        $container->addDefinition(Definition::create('monolog.processor.closure-context', ClosureContextProcessor::class, new ClosureContextProcessorFactory()));
        $container->addDefinition(Definition::create('monolog.processor.hostname', HostnameProcessor::class, new HostnameProcessorFactory()));
        $container->addDefinition(Definition::create('monolog.processor.load-average', LoadAverageProcessor::class, new LoadAverageProcessorFactory()));
        $container->addDefinition(Definition::create('monolog.processor.memory-peak-usage', MemoryPeakUsageProcessor::class, new MemoryPeakUsageProcessorFactory()));
        $container->addDefinition(Definition::create('monolog.processor.memory-usage', MemoryUsageProcessor::class, new MemoryUsageProcessorFactory()));
        $container->addDefinition(Definition::create('monolog.processor.process-id', ProcessIdProcessor::class, new ProcessIdProcessorFactory()));
        $container->addDefinition(Definition::create('monolog.processor.psr-log-message', PsrLogMessageProcessor::class, new PsrLogMessageProcessorFactory()));
    }

    /**
     * @param ContainerBuilderInterface $container
     * @param Configuration $configuration
     */
    private function registerFormatters(ContainerBuilderInterface $container, array $configuration): void
    {
        $container->addDefinition(Definition::create('monolog.formatter.console', ConsoleFormatter::class, new ConsoleFormatterFactory(
            $configuration['formatters']['console']['format'] ?? null,
            $configuration['formatters']['console']['date-format'] ?? null,
            $configuration['formatters']['console']['allow-inline-line-breaks'] ?? null,
            $configuration['formatters']['console']['ignore-empty-context-and-extra'] ?? null,
        )));

        $container->addDefinition(Definition::create('monolog.formatter.html', HtmlFormatter::class, new HtmlFormatterFactory(
            $configuration['formatters']['html']['date-format'] ?? null,
        )));

        $container->addDefinition(Definition::create('monolog.formatter.json', JsonFormatter::class, new JsonFormatterFactory(
            $configuration['formatters']['json']['batch-mode'] ?? null,
            $configuration['formatters']['json']['append-newline'] ?? null,
            $configuration['formatters']['json']['ignore-empty-context-and-extra'] ?? null,
            $configuration['formatters']['json']['include-stack-traces'] ?? null
        )));

        $container->addDefinition(Definition::create('monolog.formatter.line', LineFormatter::class, new LineFormatterFactory(
            $configuration['formatters']['line']['format'] ?? null,
            $configuration['formatters']['line']['date-format'] ?? null,
            $configuration['formatters']['line']['allow-inline-line-breaks'] ?? null,
            $configuration['formatters']['line']['ignore-empty-context-and-extra'] ?? null,
            $configuration['formatters']['line']['include-stack-traces'] ?? null
        )));

        $container->addDefinition(Definition::create('monolog.formatter.normalizer', NormalizerFormatter::class, new NormalizerFormatterFactory(
            $configuration['formatters']['normalizer']['date-format'] ?? null,
        )));

        $container->addDefinition(Definition::create('monolog.formatter.scalar', ScalarFormatter::class, new ScalarFormatterFactory(
            $configuration['formatters']['scalar']['date-format'] ?? null,
        )));
    }

    /**
     * @param ContainerBuilderInterface $container
     * @param Configuration $configuration
     */
    private function registerHandlers(ContainerBuilderInterface $container, array $configuration): void
    {
        $this->registerNullHandler($container, 'null', ['type' => 'null']);

        $this->registerStderrHandler($container, 'stderr', [
            'type' => 'stderr',
            'formatter' => 'monolog.formatter.console',
            'processors' => [
                'monolog.processor.process-id',
                'monolog.processor.psr-log-message',
            ]
        ]);

        $this->registerStdoutHandler($container, 'stdout', [
            'type' => 'stdout',
            'formatter' => 'monolog.formatter.console',
            'processors' => [
                'monolog.processor.process-id',
                'monolog.processor.psr-log-message',
            ]
        ]);

        $handlers = $configuration['handlers'] ?? [];
        foreach ($handlers as $name => $handler) {
            if ($handler['type'] === 'null') {
                /** @var NullHandlerConfiguration $handler */
                $this->registerNullHandler($container, $name, $handler);
            } elseif ($handler['type'] === 'stdout') {
                /** @var StdoutHandlerConfiguration $handler */
                $this->registerStdoutHandler($container, $name, $handler);
            } elseif ($handler['type'] === 'stderr') {
                /** @var StderrHandlerConfiguration $handler */
                $this->registerStderrHandler($container, $name, $handler);
            } else {
                /** @var FileHandlerConfiguration $handler */
                $this->registerFileHandler($container, $name, $handler);
            }
        }
    }

    /**
     * @param ContainerBuilderInterface $container
     * @param NullHandlerConfiguration $configuration
     */
    private function registerNullHandler(ContainerBuilderInterface $container, string $name, array $configuration): void
    {
        $container->addDefinition(Definition::create('monolog.handler.' . $name, NullHandler::class, new NullHandlerFactory(
            level: $configuration['level'] ?? null,
        )));
    }

    /**
     * @param ContainerBuilderInterface $container
     * @param StdoutHandlerConfiguration $configuration
     */
    private function registerStdoutHandler(ContainerBuilderInterface $container, string $name, array $configuration): void
    {
        $container->addDefinition(Definition::create('monolog.handler.' . $name, StreamHandler::class, new StdoutHandlerFactory(
            level: $configuration['level'] ?? null,
            bubble: $configuration['bubble'] ?? null,
            formatter: $configuration['formatter'] ?? null,
            processors: $configuration['processors'] ?? null,
        )));
    }

    /**
     * @param ContainerBuilderInterface $container
     * @param StderrHandlerConfiguration $configuration
     */
    private function registerStderrHandler(ContainerBuilderInterface $container, string $name, array $configuration): void
    {
        $container->addDefinition(Definition::create('monolog.handler.' . $name, StreamHandler::class, new StderrHandlerFactory(
            level: $configuration['level'] ?? null,
            bubble: $configuration['bubble'] ?? null,
            formatter: $configuration['formatter'] ?? null,
            processors: $configuration['processors'] ?? null,
        )));
    }

    /**
     * @param ContainerBuilderInterface $container
     * @param FileHandlerConfiguration $configuration
     */
    private function registerFileHandler(ContainerBuilderInterface $container, string $name, array $configuration): void
    {
        $container->addDefinition(Definition::create('monolog.handler.' . $name, StreamHandler::class, new FileHandlerFactory(
            file: $configuration['file'],
            level: $configuration['level'] ?? null,
            bubble: $configuration['bubble'] ?? null,
            formatter: $configuration['formatter'] ?? null,
            processors: $configuration['processors'] ?? null,
        )));
    }

    /**
     * @param ContainerBuilderInterface $container
     * @param Configuration $configuration
     *
     * @return non-empty-array<non-empty-string, non-empty-string>
     */
    private function registerChannels(ContainerBuilderInterface $container, array $configuration): array
    {
        $registered = [];
        $channels = $configuration['channels'] ?? [];
        foreach ($channels as $name => $channel) {
            $serviceId = 'monolog.logger.' . $name;

            $container->addDefinition(Definition::create($serviceId, Logger::class, new LoggerFactory(
                channel: $name,
                timezone: $channel['timezone'] ?? null,
                handlers: $channel['handlers'] ?? null,
                processors: $channel['processors'] ?? null,
                useLoggingLoopDetection: $channel['use-logging-loop-detection'] ?? null,
                useMicrosecondTimestamps: $channel['use-microsecond-timestamps'] ?? null,
            )));

            $registered[$name] = $serviceId;
        }

        if ([] === $registered) {
            $container->addDefinition(Definition::create('monolog.logger.default', Logger::class, new LoggerFactory(
                channel: 'default',
                handlers: ['monolog.handler.stderr'],
            )));

            $registered['default'] = 'monolog.logger.default';
        }

        return $registered;
    }
}
