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

namespace Neu\Component\Http\Exception;

use Neu\Component\Http\Message\StatusCode;
use Throwable;

final class ForbiddenException extends HttpException
{
    /**
     * @param array<non-empty-string, non-empty-list<non-empty-string>> $headers
     */
    public function __construct(array $headers = [], string $message = 'Forbidden', null|Throwable $previous = null)
    {
        parent::__construct(StatusCode::Forbidden, $headers, $message, $previous);
    }
}
