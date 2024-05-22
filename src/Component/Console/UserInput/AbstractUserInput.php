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

namespace Neu\Component\Console\UserInput;

use Neu\Component\Console\Exception\InvalidArgumentException;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;

/**
 * `AbstractUserInput` handles core functionality for prompting and accepting
 * the user input.
 *
 * @template T
 *
 * @implements UserInputInterface<T>
 */
abstract class AbstractUserInput implements UserInputInterface
{
    /**
     * Input values accepted to continue.
     *
     * @var array<non-empty-string, T>
     */
    protected array $acceptedValues = [];

    /**
     * Default value if input given is empty.
     *
     * @var null|non-empty-string
     */
    protected null|string $default = null;

    /**
     * Display position.
     *
     * @var null|array{0: int<0, max>, 1: int<0, max>}
     */
    protected null|array $position = null;

    /**
     * Construct a new `UserInput` object.
     */
    public function __construct(
        /**
         * The `InputInterface` object used for retrieving user input.
         */
        protected InputInterface   $input,
        /**
         * The `OutputInterface` object used for sending output.
         */
        protected OutputInterface $output,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function setPosition(null|array $position): void
    {
        $this->position = $position;
    }

    /**
     * Set the values accepted by the user.
     *
     * @param array<non-empty-string, T> $values
     */
    public function setAcceptedValues(array $values = []): self
    {
        $this->acceptedValues = $values;

        return $this;
    }

    /**
     * Set the default value to use when input is empty.
     *
     * @param null|non-empty-string $default
     *
     * @throws InvalidArgumentException If the default value is not one of the accepted values.
     */
    public function setDefault(null|string $default): self
    {
        if (null !== $default && !isset($this->acceptedValues[$default])) {
            throw new InvalidArgumentException(
                'Default value must be one of the accepted values.'
            );
        }

        $this->default = $default;

        return $this;
    }
}
