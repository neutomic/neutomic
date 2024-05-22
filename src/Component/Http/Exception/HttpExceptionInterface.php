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

interface HttpExceptionInterface extends ExceptionInterface
{
    /**
     * Returns the status code.
     *
     * @return StatusCode An HTTP response status code
     */
    public function getStatusCode(): StatusCode;

    /**
     * Returns response headers.
     *
     * @return array<non-empty-string, non-empty-string|non-empty-list<non-empty-string>> Response headers
     */
    public function getHeaders(): array;
}
