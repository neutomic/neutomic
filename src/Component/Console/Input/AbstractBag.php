<?php

declare(strict_types=1);

namespace Neu\Component\Console\Input;

use Neu\Component\Console\Bag;
use Neu\Component\Console\Input\Definition\DefinitionInterface;

/**
 * @template T of DefinitionInterface
 *
 * @extends Bag\AbstractBag<string, T>
 */
class AbstractBag extends Bag\AbstractBag
{
    /**
     * @param list<T> $data
     */
    public function __construct(array $data = [])
    {
        $raw = [];
        foreach ($data as $definition) {
            $raw[$definition->getName()] = $definition;
            if ($definition->getAlias() !== '') {
                $raw[$definition->getAlias()] = $definition;
            }
        }

        parent::__construct($raw);
    }

    /**
     * Add a definition object to the bag.
     *
     * @param T $definition
     */
    public function addDefinition(DefinitionInterface $definition): void
    {
        $this->data[$definition->getName()] = $definition;
        $this->data[$definition->getAlias()] = $definition;
    }

    /**
     * Retrieve the definition object based on the given key.
     *
     * The key is checked against all available names as well as aliases.
     *
     * @param string $key
     * @param null|T $default
     *
     * @return T
     */
    public function get(string|int $key, mixed $default = null): mixed
    {
        $value = parent::get($key, $default);
        if ($value === null) {
            foreach ($this as $definition) {
                if ($key === $definition->getAlias()) {
                    /** @var T */
                    return $definition;
                }
            }
        }

        /** @var T */
        return $value;
    }
}
