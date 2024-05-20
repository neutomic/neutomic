<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection\Exception;

use Psr\Container\NotFoundExceptionInterface;

final class ServiceNotFoundException extends RuntimeException implements ExceptionInterface, NotFoundExceptionInterface
{
    /**
     * Create a new instance for a service id.
     *
     * @param string $id The service id.
     */
    public static function forService(string $id): self
    {
        return new self('Service "' . $id . '" is not found.');
    }
}
