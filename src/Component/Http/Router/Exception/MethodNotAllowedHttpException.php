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

namespace Neu\Component\Http\Router\Exception;

use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Message\UriInterface;
use Psl\Str;
use Psl\Vec;
use Throwable;

final class MethodNotAllowedHttpException extends HttpException
{
    /**
     * @var non-empty-list<Method>
     */
    private array $allowed;

    /**
     * @param non-empty-list<Method> $allowed
     */
    public function __construct(array $allowed, string $message = '', null|Throwable $previous = null)
    {
        $headers = [
            'Allow' => Vec\map(
                $allowed,
                /**
                 * @param Method $method
                 *
                 * @return non-empty-string
                 */
                static fn (Method $method): string => $method->value,
            ),
        ];

        parent::__construct(StatusCode::MethodNotAllowed, $headers, $message, $previous);

        $this->allowed = $allowed;
    }

    /**
     * Creates a new instance of the exception.
     *
     * @param non-empty-list<Method> $allowed
     */
    public static function create(Method $method, UriInterface $uri, array $allowed, null|Throwable $previous = null): self
    {
        $allowedString = Str\join(Vec\map($allowed, static fn (Method $method): string => $method->value), '", "');

        return new self(
            $allowed,
            'Method "' . $method->value . '" is not allowed for "' . $uri->getPath() . '", allowed methods: ' . $allowedString,
            $previous,
        );
    }

    /**
     * Returns the allowed methods.
     *
     * @return non-empty-list<Method>
     */
    public function getAllowedMethods(): array
    {
        return $this->allowed;
    }
}
