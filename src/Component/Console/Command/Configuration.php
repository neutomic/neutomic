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

use Neu\Component\Console\Exception\InvalidArgumentException;
use Neu\Component\Console\Input\Bag\ArgumentBag;
use Neu\Component\Console\Input\Bag\FlagBag;
use Neu\Component\Console\Input\Bag\OptionBag;
use Neu\Component\Console\Input\Definition\Argument;
use Neu\Component\Console\Input\Definition\Flag;
use Neu\Component\Console\Input\Definition\Option;
use Psl\Regex;
use Psl\Str;

/**
 * The configuration for a command.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class Configuration
{
    private const string NAME_PATTERN = "/^[^\:]++(\:[^\:]++)*$/";

    /**
     * The name of the command passed into the command line.
     *
     * @var non-empty-string
     */
    public string $name;

    /**
     * The description of the command used when rendering its help screen.
     *
     * @var string
     */
    public string $description;

    /**
     * The aliases for the command name.
     *
     * @var list<non-empty-string>
     */
    public array $aliases;

    /**
     * Bag container holding all registered `Flag` objects.
     */
    public FlagBag $flags;

    /**
     * Bag container holding all registered `Option` objects.
     */
    public OptionBag $options;

    /**
     * Bag container holding all registered `Argument` objects.
     */
    public ArgumentBag $arguments;

    /**
     * Whether the command should be publicly shown or not.
     */
    public bool $hidden;

    /**
     * Whether the command is enabled or not.
     */
    public bool $enabled;

    /**
     * Create a new command configuration instance.
     *
     * @param non-empty-string $name
     * @param string $description
     * @param list<non-empty-string> $aliases
     *
     * @throws InvalidArgumentException If the name or alias contains invalid characters.
     */
    public function __construct(
        string $name,
        string $description,
        array $aliases = [],
        FlagBag $flags = new FlagBag(),
        OptionBag $options = new OptionBag(),
        ArgumentBag $arguments = new ArgumentBag(),
        bool $hidden = false,
        bool $enabled = true,
    ) {
        if (!Regex\matches($name, self::NAME_PATTERN)) {
            throw new InvalidArgumentException(
                Str\format('Command name "%s" is invalid.', $name),
            );
        }

        foreach ($aliases as $alias) {
            if (!Regex\matches($alias, self::NAME_PATTERN)) {
                throw new InvalidArgumentException(
                    Str\format('Command alias "%s" is invalid.', $alias),
                );
            }
        }

        $this->name = $name;
        $this->description = $description;
        $this->aliases = $aliases;
        $this->flags = $flags;
        $this->options = $options;
        $this->arguments = $arguments;
        $this->hidden = $hidden;
        $this->enabled = $enabled;
    }

    /**
     * Create a new command configuration instance.
     *
     * @param non-empty-string $name The name of the command.
     * @param string $description The description of the command.
     *
     * @throws InvalidArgumentException If the name contains invalid characters.
     */
    public static function create(string $name, string $description): self
    {
        return new self(
            $name,
            $description,
        );
    }

    /**
     * Return a new instance with the provided name.
     *
     * @param non-empty-string $name
     *
     * @throws InvalidArgumentException If the name contains invalid characters.
     */
    public function withName(string $name): self
    {
        return new self(
            $name,
            $this->description,
            $this->aliases,
            $this->flags,
            $this->options,
            $this->arguments,
            $this->hidden,
            $this->enabled,
        );
    }

    /**
     * Return a new instance with the provided description.
     */
    public function withDescription(string $description): self
    {
        return new self(
            $this->name,
            $description,
            $this->aliases,
            $this->flags,
            $this->options,
            $this->arguments,
            $this->hidden,
            $this->enabled,
        );
    }

    /**
     * Return a new instance with the provided aliases.
     *
     * @param list<non-empty-string> $aliases
     *
     * @throws InvalidArgumentException If any of the aliases contain invalid characters.
     */
    public function withAliases(array $aliases): self
    {
        return new self(
            $this->name,
            $this->description,
            $aliases,
            $this->flags,
            $this->options,
            $this->arguments,
            $this->hidden,
            $this->enabled,
        );
    }

    /**
     * Return a new instance with the provided alias added.
     *
     * @param non-empty-string $alias
     *
     * @throws InvalidArgumentException If the alias contains invalid characters.
     */
    public function withAddedAlias(string $alias): self
    {
        $aliases = $this->aliases;
        $aliases[] = $alias;

        return new self(
            $this->name,
            $this->description,
            $aliases,
            $this->flags,
            $this->options,
            $this->arguments,
            $this->hidden,
            $this->enabled,
        );
    }

    /**
     * Return a new instance with the provided flags.
     */
    public function withFlags(FlagBag $flags): self
    {
        return new self(
            $this->name,
            $this->description,
            $this->aliases,
            $flags,
            $this->options,
            $this->arguments,
            $this->hidden,
            $this->enabled,
        );
    }

    /**
     * Return a new instance with the provided flag definition added.
     */
    public function withAddedFlag(Flag $flag): self
    {
        $flags = $this->flags;
        $flags->addDefinition($flag);

        return new self(
            $this->name,
            $this->description,
            $this->aliases,
            $flags,
            $this->options,
            $this->arguments,
            $this->hidden,
            $this->enabled,
        );
    }

    /**
     * Return a new instance with the provided options.
     */
    public function withOptions(OptionBag $options): self
    {
        return new self(
            $this->name,
            $this->description,
            $this->aliases,
            $this->flags,
            $options,
            $this->arguments,
            $this->hidden,
            $this->enabled,
        );
    }

    /**
     * Return a new instance with the provided option definition added.
     */
    public function withAddedOption(Option $option): self
    {
        $options = $this->options;
        $options->addDefinition($option);

        return new self(
            $this->name,
            $this->description,
            $this->aliases,
            $this->flags,
            $options,
            $this->arguments,
            $this->hidden,
            $this->enabled,
        );
    }

    /**
     * Return a new instance with the provided arguments.
     */
    public function withArguments(ArgumentBag $arguments): self
    {
        return new self(
            $this->name,
            $this->description,
            $this->aliases,
            $this->flags,
            $this->options,
            $arguments,
            $this->hidden,
            $this->enabled,
        );
    }

    /**
     * Return a new instance with the provided argument definition added.
     */
    public function withAddedArgument(Argument $argument): self
    {
        $arguments = $this->arguments;
        $arguments->addDefinition($argument);

        return new self(
            $this->name,
            $this->description,
            $this->aliases,
            $this->flags,
            $this->options,
            $arguments,
            $this->hidden,
            $this->enabled,
        );
    }

    /**
     * Return a new instance with the provided hidden value.
     */
    public function withHidden(bool $hidden): self
    {
        return new self(
            $this->name,
            $this->description,
            $this->aliases,
            $this->flags,
            $this->options,
            $this->arguments,
            $hidden,
            $this->enabled,
        );
    }

    /**
     * Return a new instance with the provided enabled value.
     */
    public function withEnabled(bool $enabled): self
    {
        return new self(
            $this->name,
            $this->description,
            $this->aliases,
            $this->flags,
            $this->options,
            $this->arguments,
            $this->hidden,
            $enabled,
        );
    }
}
