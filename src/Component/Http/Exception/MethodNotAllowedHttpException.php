<?php

declare(strict_types=1);

namespace Neu\Component\Http\Exception;

use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Message\UriInterface;
use Throwable;

use function array_map;

final class MethodNotAllowedHttpException extends RuntimeException implements HttpExceptionInterface
{
    /**
     * @param list<Method> $allowed
     */
    private array $allowed;

    /**
     * @param list<Method> $allowed
     */
    public function __construct(array $allowed, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->allowed = $allowed;
    }

    /**
     * Creates a new instance of the exception.
     *
     * @param list<Method> $allowed
     */
    public static function create(Method $method, UriInterface $uri, array $allowed, ?Throwable $previous = null): self
    {
        $allowedString = implode(', ', array_map(static fn (Method $method): string => $method->value, $allowed));

        return new self(
            $allowed,
            'Method "' . $method->value . '" is not allowed for "' . $uri->getPath() . '", allowed methods: ' . $allowedString,
            0,
            $previous,
        );
    }

    /**
     * Returns the allowed methods.
     *
     * @return list<Method>
     */
    public function getAllowedMethods(): array
    {
        return $this->allowed;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): StatusCode
    {
        return StatusCode::MethodNotAllowed;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return ['Allow' =>  array_map(static fn (Method $method): string => $method->value, $this->allowed)];
    }
}
