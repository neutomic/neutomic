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

use Neu\Component\Console\Bag;
use Neu\Component\Console\Input\Definition\DefinitionInterface;
use Override;

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
            $alias = $definition->getAlias();
            if ($alias !== null) {
                $raw[$alias] = $definition;
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
        $name = $definition->getName();
        $alias = $definition->getAlias();

        $this->data[$name] = $definition;
        if ($alias !== null) {
            $this->data[$alias] = $definition;
        }
    }

    /**
     * Retrieve the definition object based on the given key.
     *
     * The key is checked against all available names as well as aliases.
     *
     * @param string $key
     * @param null|T $default
     *
     * @return null|T
     */
    #[Override]
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

        /** @var null|T */
        return $value;
    }
}
