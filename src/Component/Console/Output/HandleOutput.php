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

namespace Neu\Component\Console\Output;

use Neu\Component\Console\Formatter\FormatterInterface;
use Psl\IO;

/**
 * An {@see OutputInterface} implementation based on {@see IO\WriteHandleInterface}.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class HandleOutput extends AbstractOutput
{
    private readonly IO\WriteHandleInterface $outputHandle;

    /**
     * Construct a new {@see HandleOutput} object.
     */
    public function __construct(IO\WriteHandleInterface $outputHandle, Verbosity $verbosity = Verbosity::Normal, null|bool $decorated = null, null|FormatterInterface $formatter = null)
    {
        parent::__construct($verbosity, $decorated, $formatter);

        $this->outputHandle = $outputHandle;
    }

    /**
     * @inheritDoc
     */
    protected function doWrite(string $content): void
    {
        $this->outputHandle->writeAll($content);
    }

    /**
     * @inheritDoc
     */
    public function getStream(): mixed
    {
        if ($this->outputHandle instanceof IO\StreamHandleInterface) {
            return $this->outputHandle->getStream();
        }

        return null;
    }
}
