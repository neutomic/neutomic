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

namespace Neu\Component\Http\Runtime\Exception;

use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Runtime\ContentDelivery\ContentDeliverer;
use Throwable;

/**
 * Exception thrown when a requested file is not found.
 *
 * @see ContentDeliverer::deliver() For the context in which this exception is thrown.
 */
final class FileNotFoundHttpException extends HttpException
{
    /**
     * Constructs a new FileNotFoundHttpException.
     *
     * @param string $message The exception message (optional).
     * @param null|Throwable $previous The previous throwable used for exception chaining (optional).
     */
    public function __construct(string $message = '', null|Throwable $previous = null)
    {
        parent::__construct(StatusCode::NotFound, [], $message, $previous);
    }

    /**
     * Create a new instance of {@see FileNotFoundHttpException} for the case
     * where the file is not found.
     *
     * @param string $file The path of the file that was not found.
     */
    public static function create(string $file): self
    {
        return new self('The requested file "' . $file . '" was not found.');
    }

    /**
     * Create a new instance of {@see FileNotFoundHttpException} for the case
     * where the request path is a directory instead of a file.
     *
     * @param string $directory The path of the directory that was requested as a file.
     */
    public static function isDirectory(string $directory): self
    {
        return new self('The requested path "' . $directory . '" is a directory, not a file.');
    }
}
