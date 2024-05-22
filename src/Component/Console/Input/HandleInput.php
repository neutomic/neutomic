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

namespace Neu\Component\Console\Input;

use Neu\Component\Console\Exception\NonInteractiveInputException;
use Psl\IO;
use Psl\Str;

/**
 * A {@see InputInterface} implementation based on {@see IO\ReadHandleInterface}.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class HandleInput extends AbstractInput
{
    /**
     * Buffered reader for user input.
     */
    private IO\Reader $reader;

    /**
     * Construct a new instance of {@see HandleInput}.
     *
     * @param list<string> $args
     */
    public function __construct(IO\ReadHandleInterface $handle, array $args)
    {
        parent::__construct($args);

        $this->reader = new IO\Reader($handle);
    }

    /**
     * @inheritDoc
     */
    public function getUserInput(null|int $length = null): string
    {
        if (!$this->isInteractive()) {
            throw new NonInteractiveInputException('The current terminal session is non interactive.');
        }

        if ($length !== null) {
            return $this->reader->readFixedSize($length);
        }

        return Str\trim($this->reader->readLine() ?? '');
    }

    /**
     * @inheritDoc
     */
    public function getStream(): mixed
    {
        $handle = $this->reader->getHandle();
        if ($handle instanceof IO\StreamHandleInterface) {
            return $handle->getStream();
        }

        return null;
    }
}
