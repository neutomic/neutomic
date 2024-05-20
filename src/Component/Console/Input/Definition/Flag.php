<?php

declare(strict_types=1);

namespace Neu\Component\Console\Input\Definition;

use Psl\Str;

/**
 * A `Flag` is a boolean parameter (denoted by an integer) specified by a user.
 *
 * @extends Definition<int>
 */
final class Flag extends Definition
{
    /**
     * Whether the flag is stackable or not (i.e., -fff is given a value of 3).
     */
    private bool $stackable;

    /**
     * The negative alias of the `Flag` (i.e., --no-foo for -foo). A negative
     * value is only available if a 'long' `Flag` name is available.
     */
    private string $negativeAlias = '';

    /**
     * Construct a new `Flag` object.
     */
    public function __construct(string $name, null|string $alias = null, string $description = '', Mode $mode = Mode::Optional, bool $stackable = false)
    {
        parent::__construct($name, $alias, $description, $mode);

        $this->stackable = $stackable;

        if (Str\length($name) > 1) {
            $this->negativeAlias = 'no-' . $name;
        }
    }

    /**
     * Retrieve the negative alias of the `Flag` or null of none.
     */
    public function getNegativeAlias(): string
    {
        return $this->negativeAlias;
    }

    /**
     * If the `Flag` is stackable, increase its value for each occurrence of the
     * flag.
     */
    public function increaseValue(): self
    {
        $this->exists = true;
        if ($this->stackable) {
            if ($this->value === null) {
                $this->value = 1;
            } else {
                $this->value++;
            }
        }

        return $this;
    }

    /**
     * Retrieve whether the `Flag` is stackable or not.
     */
    public function isStackable(): bool
    {
        return $this->stackable;
    }

    /**
     * Set whether the `Flag` is stackable or not.
     */
    public function setStackable(bool $stackable): self
    {
        $this->stackable = $stackable;

        return $this;
    }

    /**
     * Set an alias for the `Flag`. If the 'name' given at construction is a short
     * name and the alias set is long, the 'alias' given here will serve as the
     * 'name' and the original name will be set to the 'alias'.
     */
    public function alias(string $alias): self
    {
        parent::alias($alias);

        if (Str\length($this->getName()) > 1) {
            $this->negativeAlias = 'no-' . $this->getName();
        }

        return $this;
    }
}
