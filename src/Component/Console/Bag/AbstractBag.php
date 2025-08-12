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

namespace Neu\Component\Console\Bag;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Psl\Iter;

/**
 * A bag can be used for managing sets of specialized data.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @implements IteratorAggregate<Tk, Tv>
 */
abstract class AbstractBag implements Countable, IteratorAggregate
{
    /**
     * @var array<Tk, Tv>
     */
    protected array $data = [];

    /**
     * Set the parameters.
     *
     * @param array<Tk, Tv> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Return all parameters and their values within the bag.
     *
     * @return array<Tk, Tv>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Remove all values within the bag.
     */
    public function flush(): void
    {
        $this->data = [];
    }

    /**
     * Return a value defined by key, or by dot notated path.
     * If no key is found, return null, or if there is no value,
     * return the default value.
     *
     * @param Tk $key
     * @param Tv|null $default
     *
     * @return Tv|null
     */
    public function get(string|int $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if a key exists within the bag.
     * Can use a dot notated path as the key.
     *
     * @param Tk $key
     */
    public function has(string|int $key): bool
    {
        return Iter\contains_key($this->data, $key);
    }

    /**
     * Remove a value defined by key, or dot notated path.
     *
     * @param Tk $key
     */
    public function remove(string|int $key): static
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
     * Set a value defined by key. Can pass in a dot notated path
     * to insert into a nested structure.
     *
     * @param Tk $key
     * @param Tv $value
     */
    public function set(string|int $key, mixed $value): void
    {
        $this->add([$key => $value]);
    }

    /**
     * Add multiple parameters that will overwrite any previously defined parameters.
     *
     * @param array<Tk, Tv> $data
     */
    public function add(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Returns an iterator to be used to iterate over the object's elements.
     *
     * @return Iterator<Tk, Tv>
     */
    #[\Override]
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->data);
    }

    #[\Override]
    public function count(): int
    {
        return Iter\count($this->data);
    }
}
