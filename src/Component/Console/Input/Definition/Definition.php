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

namespace Neu\Component\Console\Input\Definition;

use Neu\Component\Console\Exception\MissingValueException;
use Psl\Str;

/**
 * A {@see Definition} is an object that designates the parameters accepted by
 * the user when executing commands.
 *
 * @template T
 *
 * @implements DefinitionInterface<T>
 */
abstract class Definition implements DefinitionInterface
{
    /**
     * The name and primary method to specify the input.
     *
     * @var non-empty-string
     */
    protected string $name;

    /**
     * An alternate notation to specify the input as.
     *
     * @var null|non-empty-string
     */
    protected null|string $alias = null;

    /**
     * The description of the input.
     */
    protected string $description = '';

    /**
     * The mode of the input to determine if it should be required by the user.
     */
    protected Mode $mode = Mode::Optional;

    /**
     * Flag if the {@see Definition} has been assigned a value.
     */
    protected bool $exists = false;

    /**
     * The value the user has given the input.
     *
     * @var null|T
     */
    protected mixed $value = null;

    /**
     * Construct a new instance of an {@see Definition}.
     *
     * @param non-empty-string $name The name of the definition.
     * @param null|non-empty-string $alias An alias for the definition.
     */
    public function __construct(string $name, null|string $alias = null, string $description = '', Mode $mode = Mode::Optional)
    {
        $this->name = $name;
        $this->alias = $alias;
        $this->description = $description;
        $this->mode = $mode;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getAlias(): null|string
    {
        return $this->alias;
    }

    /**
     * Set the alias of the {@see Definition}.
     *
     * @param non-empty-string $alias
     */
    public function alias(string $alias): self
    {
        if (Str\Byte\length($alias) > Str\Byte\length($this->name)) {
            $this->alias = $this->name;
            $this->name = $alias;
        } else {
            $this->alias = $alias;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getFormattedName(string $name): string
    {
        if (Str\Byte\length($name) === 1) {
            return '-' . $name;
        }

        return '--' . $name;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getMode(): Mode
    {
        return $this->mode;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getValue(): mixed
    {
        if ($this->exists) {
            return $this->value;
        }

        throw new MissingValueException(Str\format('The `%s` ( "%s" ) definition has not been assigned a value.', $this::class, $this->getName()));
    }

    /**
     * Set the value of the {@see Definition}.
     *
     * @param T $value
     */
    public function assign(mixed $value): static
    {
        $this->value = $value;
        $this->exists = true;

        return $this;
    }
}
