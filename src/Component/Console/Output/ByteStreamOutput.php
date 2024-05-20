<?php

declare(strict_types=1);

namespace Neu\Component\Console\Output;

use Amp\ByteStream;
use Neu\Component\Console\Formatter\FormatterInterface;

/**
 * An {@see OutputInterface} implementation based on {@see ByteStream\WritableStream}.
 */
final class ByteStreamOutput extends AbstractOutput
{
    private readonly ByteStream\WritableStream $outputStream;

    /**
     * Construct a new {@see ByteStreamOutput} object.
     */
    public function __construct(ByteStream\WritableStream $outputStream, Verbosity $verbosity = Verbosity::Normal, ?bool $decorated = null, ?FormatterInterface $formatter = null)
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
