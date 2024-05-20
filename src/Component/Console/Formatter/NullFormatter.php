<?php

declare(strict_types=1);

namespace Neu\Component\Console\Formatter;

final class NullFormatter extends AbstractFormatter
{
    /**
     * @inheritDoc
     */
    public function format(string $message, int $width = 0): string
    {
        return $message;
    }
}
