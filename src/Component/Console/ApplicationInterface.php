<?php

declare(strict_types=1);

namespace Neu\Component\Console;

use Neu\Component\Console\Command\Registry\RegistryInterface;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\Console\Recovery\RecoveryInterface;
use Neu\Component\EventDispatcher\EventDispatcherAwareInterface;
use Neu\Component\EventDispatcher\EventDispatcherInterface;

interface ApplicationInterface extends EventDispatcherAwareInterface
{
    /**
     * Get the configuration for the application.
     *
     * @return Configuration The configuration object.
     */
    public function getConfiguration(): Configuration;

    /**
     * Get the command registry for the application.
     *
     * @return RegistryInterface The command registry object.
     */
    public function getRegistry(): RegistryInterface;

    /**
     * Get the recovery handler for the application.
     *
     * @return RecoveryInterface The recovery handler object.
     */
    public function getRecovery(): RecoveryInterface;

    /**
     * Get the event dispatcher for the application.
     *
     * @return null|EventDispatcherInterface The event dispatcher object, or null if none is set.
     */
    public function getEventDispatcher(): null|EventDispatcherInterface;

    /**
     * Run the application with the given input and output.
     *
     * @param null|InputInterface $input The input object containing all registered and parsed command line parameters, or null to use the default input.
     * @param null|OutputInterface $output The output object to handle output to the user, or null to use the default output.
     *
     * @return int The exit status code.
     */
    public function run(null|InputInterface $input = null, null|OutputInterface $output = null): int;
}
