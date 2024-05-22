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

use Amp\ByteStream;
use Neu\Component\Console\Formatter\FormatterInterface;

/**
 * An {@see OutputInterface} implementation based on {@see ByteStream\WritableStream}.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class ByteStreamOutput extends AbstractOutput
{
    private readonly ByteStream\WritableStream $outputStream;

    /**
     * Construct a new {@see ByteStreamOutput} object.
     */
    public function __construct(ByteStream\WritableStream $outputStream, Verbosity $verbosity = Verbosity::Normal, null|bool $decorated = null, null|FormatterInterface $formatter = null)
    {
        parent::__construct($verbosity, $decorated, $formatter);

        $this->outputStream = $outputStream;
    }

    /**
     * @inheritDoc
     */
    protected function doWrite(string $content): void
    {
        $this->outputStream->write($content);
    }

    /**
     * @inheritDoc
     */
    public function getStream(): mixed
    {
        if ($this->outputStream instanceof ByteStream\ResourceStream) {
            return $this->outputStream->getResource();
        }

        return null;
    }
}
