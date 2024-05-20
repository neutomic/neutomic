<?php

declare(strict_types=1);

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
 * @implements UserInputInterface
 */
abstract class AbstractUserInput implements UserInputInterface
{
    /**
     * Input values accepted to continue.
     *
     * @var array<string, T>
     */
    protected array $acceptedValues = [];

    /**
     * Default value if input given is empty.
     *
     * @var null|non-empty-string
     */
    protected ?string $default = null;

    /**
     * Display position.
     *
     * @var null|array{0: int, 1: int}
     */
    protected ?array $position = null;

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
     * Set the display position (column, row).
     *
     * Implementation should not change position unless this method
     * is called.
     *
     * When changing positions, the implementation should always save the cursor
     * position, then restore it.
     *
     * @param null|array{0: int, 1: int}
     */
    public function setPosition(?array $position): void
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
     */
    public function setDefault(?string $default): self
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
