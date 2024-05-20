<?php

declare(strict_types=1);

namespace Neu\Component\Console\Attribute;

use Attribute;
use Neu\Component\Console\Command\Configuration;
use Neu\Component\Console\Input\Bag\ArgumentBag;
use Neu\Component\Console\Input\Bag\FlagBag;
use Neu\Component\Console\Input\Bag\OptionBag;
use Neu\Component\Console\Input\Definition\Argument;
use Neu\Component\Console\Input\Definition\Flag;
use Neu\Component\Console\Input\Definition\Option;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Command
{
    /**
     * The name of the command passed into the command line.
     *
     * @var non-empty-string
     */
    public string $name;

    /**
     * The description of the command used when rendering its help screen.
     *
     * @var non-empty-string
     */
    public string $description;

    /**
     * The aliases for the command name.
     *
     * @var list<non-empty-string>
     */
    public array $aliases;

    /**
     * Bag container holding all registered {@see Flag} objects.
     */
    public FlagBag $flags;

    /**
     * Bag container holding all registered {@see Option} objects.
     */
    public OptionBag $options;

    /**
     * Bag container holding all registered {@see Argument} objects.
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
     * Create a new command attribute instance.
     *
     * @param non-empty-string $name
     * @param non-empty-string $description
     * @param list<non-empty-string> $aliases
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
     * Retrieve the command configuration.
     */
    public function getConfiguration(): Configuration
    {
        return new Configuration(
            $this->name,
            $this->description,
            $this->aliases,
            $this->flags,
            $this->options,
            $this->arguments,
            $this->hidden,
            $this->enabled,
        );
    }
}
