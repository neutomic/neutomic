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
 * An {@see Option} is a value parameter specified by a user.
 *
 * @extends Definition<string>
 */
final class Option extends Definition
{
    /**
     * Create a new {@see Option} instance.
     *
     * @param non-empty-string $name The name of the option.
     * @param null|non-empty-string $alias The alias of the option.
     */
    public function __construct(string $name, null|string $alias = null, string $description = '', Mode $mode = Mode::Optional, bool $aliased = true)
    {
        parent::__construct($name, $alias, $description, $mode);

        if ($aliased && Str\length($name) > 1) {
            $alias = Str\slice($name, 0, 1);
            if ($alias !== '') {
                $this->alias($alias);
            }
        }
    }
}
