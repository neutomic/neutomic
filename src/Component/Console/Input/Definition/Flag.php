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

use Psl\Str;

/**
 * A {@see Flag} is a boolean parameter (denoted by an integer) specified by a user.
 *
 * @extends Definition<int<0, max>>
 */
final class Flag extends Definition
{
    /**
     * Whether the flag is stackable or not (i.e., -fff is given a value of 3).
     */
    private bool $stackable;

    /**
     * The negative alias of the {@see Flag} (i.e., --no-foo for -foo). A negative
     * value is only available if a 'long' {@see Flag} name is available.
     */
    private string $negativeAlias = '';

    /**
     * Construct a new {@see Flag} object.
     *
     * @param non-empty-string $name The name of the flag.
     * @param null|non-empty-string $alias The alias of the flag.
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
     * Retrieve the negative alias of the {@see Flag} or null of none.
     */
    public function getNegativeAlias(): string
    {
        return $this->negativeAlias;
    }

    /**
     * If the {@see Flag} is stackable, increase its value for each occurrence of the
     * flag.
     */
    public function increaseValue(): self
    {
        $this->exists = true;
        if ($this->stackable) {
            $value = $this->value ?? 0;
            /** @var positive-int $value */
            $value = $value + 1;

            $this->value = $value;
        }

        return $this;
    }

    /**
     * Retrieve whether the {@see Flag} is stackable or not.
     */
    public function isStackable(): bool
    {
        return $this->stackable;
    }

    /**
     * Set whether the {@see Flag} is stackable or not.
     */
    public function setStackable(bool $stackable): self
    {
        $this->stackable = $stackable;

        return $this;
    }

    /**
     * Set an alias for the {@see Flag}. If the 'name' given at construction is a short
     * name and the alias set is long, the 'alias' given here will serve as the
     * 'name' and the original name will be set to the 'alias'.
     */
    #[\Override]
    public function alias(string $alias): self
    {
        parent::alias($alias);

        if (Str\length($this->getName()) > 1) {
            $this->negativeAlias = 'no-' . $this->getName();
        }

        return $this;
    }
}
