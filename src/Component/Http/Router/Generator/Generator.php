<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\Generator;

use Exception;
use Neu\Component\Http\Router\Route\Registry\RegistryInterface;

final readonly class Generator implements GeneratorInterface
{
    private RegistryInterface $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    public function generate(string $name, array $parameters = []): never
    {
        $route = $this->registry->getRoute($name);

        throw new Exception('TODO: Implement generate() method, route: ' . $route->name . ', parameters: ' . json_encode($parameters) . '.');
    }
}
