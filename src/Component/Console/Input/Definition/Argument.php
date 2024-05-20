<?php

declare(strict_types=1);

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
    public function getFormattedName(string $name): string
    {
        return $name;
    }
}
