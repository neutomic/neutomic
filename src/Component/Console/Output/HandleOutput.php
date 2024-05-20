<?php

declare(strict_types=1);

namespace Neu\Component\Console\Output;

use Neu\Component\Console\Formatter\FormatterInterface;
use Psl\IO;

/**
 * An {@see OutputInterface} implementation based on {@see IO\WriteHandleInterface}.
 */
final class HandleOutput extends AbstractOutput
{
    private readonly IO\WriteHandleInterface $outputHandle;

    /**
     * Construct a new {@see HandleOutput} object.
     */
    public function __construct(IO\WriteHandleInterface $outputHandle, Verbosity $verbosity = Verbosity::Normal, ?bool $decorated = null, ?FormatterInterface $formatter = null)
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
