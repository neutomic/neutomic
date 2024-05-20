<?php

declare(strict_types=1);

namespace Neu\Component\Exception;

use Throwable;

/**
 * A marker interface for exceptions in the contract namespace.
 *
 * All exceptions in the contract namespace should implement this interface.
 */
interface ExceptionInterface extends Throwable
{
}
