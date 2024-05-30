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

namespace Neu\Framework\Command\Http\Server;

use Amp\Cluster\Cluster;
use Assess\Configuration;
use Assess\Event\EventType;
use Assess\Watcher;
use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Block\BlockFactoryTrait;
use Neu\Component\Console\Command\AbstractCommand;
use Neu\Component\Console\Command\ExitCode;
use Neu\Component\Console\Input\Bag\FlagBag;
use Neu\Component\Console\Input\Bag\OptionBag;
use Neu\Component\Console\Input\Definition\Flag;
use Neu\Component\Console\Input\Definition\Option;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Server\ClusterInterface;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\Str;
use Revolt\EventLoop\UnsupportedFeatureException;

/**
 * Starts the HTTP server cluster, initializing multiple worker processes to handle requests.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress MissingThrowsDocblock
 */
#[Command(
    name: 'http:server:cluster',
    description: 'Starts the HTTP server cluster, initializing multiple worker processes to handle requests.',
    flags: new FlagBag([
        new Flag('skip-watcher', 's', description: 'Skip enabling the file system watcher')
    ]),
    options: new OptionBag([
        new Option('workers', 'w', description: 'The number of workers to use'),
    ])
)]
final readonly class ClusterCommand extends AbstractCommand
{
    use BlockFactoryTrait;

    private ClusterInterface $cluster;
    private Project $project;
    private Configuration $watcherConfiguration;

    public function __construct(ClusterInterface $cluster, Project $project, Configuration $watcherConfiguration)
    {
        $this->cluster = $cluster;
        $this->project = $project;
        $this->watcherConfiguration = $watcherConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output): ExitCode
    {
        $option = $input->getOption('workers');
        if ($option->exists()) {
            $workers = Str\to_int($option->getValue());
            if (null === $workers || $workers < 1) {
                $this->createErrorBlock($output)->display(
                    'The number of workers must be an integer greater than 1.'
                );

                return ExitCode::Failure;
            }

            if (1 === $workers) {
                $this->createErrorBlock($output)->display(
                    'The number of workers must be greater than 1, use the "http:server:start" command to start the server in single-threaded mode.'
                );

                return ExitCode::Failure;
            }
        } else {
            $workers = null;
        }

        $this->cluster->start($workers);

        // Wait for the workers output to be flushed:
        Async\sleep(Duration::seconds(1));

        $this->createSuccessBlock($output)->display(
            'The server cluster has started successfully.'
        );

        if ([] !== $this->watcherConfiguration->directories) {
            $fsWatcher = Watcher::create(
                $this->watcherConfiguration
                    ->withWatchDirectories(false)
                    ->withWatchForAccess(false)
                    ->withWatchForModifications(false)
            );

            $fsWatcher->register(EventType::Created, $this->restart(...));
            $fsWatcher->register(EventType::Changed, $this->restart(...));
            $fsWatcher->register(EventType::Moved, $this->restart(...));
            $fsWatcher->register(EventType::Deleted, $this->restart(...));

            $fsWatcher->enable();

            $this->createSuccessBlock($output)->display(
                'The file system watcher has been enabled.'
            );
        }

        try {
            Cluster::awaitTermination();
        } catch (UnsupportedFeatureException) {
            $this->createWarningBlock($output)->display(
                'Signal handling is not supported on this platform. The server will not be able to gracefully shut down.'
            );
        }

        $this->cluster->stop();

        // Wait for the workers output to be flushed:
        Async\sleep(Duration::seconds(1));

        $this->createSuccessBlock($output)->display(
            'The server cluster has stopped successfully.'
        );

        return ExitCode::Success;
    }

    /**
     * Restart the server cluster.
     */
    private function restart(): void
    {
        $this->cluster->restart();

        // Wait for the workers output to be flushed:
        Async\sleep(Duration::seconds(1));

        $this->createSuccessBlock($this->output)->display(
            'The server cluster has been restarted.'
        );
    }
}
