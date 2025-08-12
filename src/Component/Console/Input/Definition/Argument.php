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

/**
 * An `Argument` is a parameter specified by the user that does not use any
 * notation (i.e., --foo, -f).
 *
 * @extends Definition<string>
 */
final class Argument extends Definition
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function getFormattedName(string $name): string
    {
        return $name;
    }
}
