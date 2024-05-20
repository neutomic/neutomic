<?php

declare(strict_types=1);

namespace Neu\Component\Console\Input\Definition;

use Psl\Str;

/**
 * An `Option` is a value parameter specified by a user.
 *
 * @extends Definition<string>
 */
final class Option extends Definition
{
    /**
     * Construct a new `Option` object.
     */
    public function __construct(string $name, null|string $alias = null, string $description = '', Mode $mode = Mode::Optional, bool $aliased = true)
    {
        parent::__construct($name, $alias, $description, $mode);

        if ($aliased && Str\length($name) > 1) {
            $this->alias(Str\slice($name, 0, 1));
        }
    }
}
