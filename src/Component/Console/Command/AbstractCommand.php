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

namespace Neu\Component\Console\Command;

use Neu\Component\Console\Block\BlockFactoryTrait;
use Neu\Component\Console\Feedback\FeedbackFactoryTrait;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\Console\Table\TableFactoryTrait;
use Neu\Component\Console\UserInput\UserInputFactoryTrait;

/**
 * A base class that simplifies the creation of new commands.
 *
 * It provides access to the application, input, and output objects, as well as
 * several traits that can be used to simplify I/O operations.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress InaccessibleProperty
 */
abstract readonly class AbstractCommand implements ApplicationAwareCommandInterface
{
    use ApplicationAwareCommandTrait;
    use BlockFactoryTrait;
    use UserInputFactoryTrait;
    use FeedbackFactoryTrait;
    use TableFactoryTrait;

    /**
     * The {@see InputInterface} object containing all registered and parsed command line
     * parameters.
     */
    protected InputInterface $input;

    /**
     * The {@see OutputInterface} object to handle output to the user.
     */
    protected OutputInterface $output;

    /**
     * The method that stores the code to be executed when the command is run.
     */
    abstract public function execute(InputInterface $input, OutputInterface $output): ExitCode|int;

    /**
     * @inheritDoc
     */
    final public function run(InputInterface $input, OutputInterface $output): ExitCode|int
    {
        $this->input = $input;
        $this->output = $output;

        return $this->execute($input, $output);
    }
}
