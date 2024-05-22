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

namespace Neu\Component\Cache;

use Closure;
use Psl\Async;

/**
 * A cache store implementation that uses a driver to store and retrieve items.
 *
 * @psalm-type SequenceComputingInput = array{(Closure(): mixed), (null|positive-int), false}
 * @psalm-type SequenceUpdatingInput = array{(Closure(mixed): mixed), (null|positive-int), true}
 */
final class Store implements StoreInterface
{
    /**
     * @var Async\KeyedSequence<non-empty-string, SequenceComputingInput|SequenceUpdatingInput, mixed>
     */
    private Async\KeyedSequence $sequence;

    public function __construct(private readonly Driver\DriverInterface $driver)
    {
        $this->sequence = new Async\KeyedSequence(
            /**
             * @param non-empty-string $key
             * @param SequenceUpdatingInput|SequenceComputingInput $input
             *
             * @return mixed
             */
            function (string $key, array $input): mixed {
                [$computer, $ttl, $update] = $input;

                $available = false;
                $existing = null;
                try {
                    /** @psalm-suppress MixedAssignment */
                    $existing = $this->driver->get($key);
                    $available = true;
                } catch (Exception\UnavailableItemException) {
                }

                if ($available && !$update) {
                    return $existing;
                }

                if ($update) {
                    /**
                     * @psalm-suppress MixedAssignment
                     * @psalm-suppress TooManyArguments
                     */
                    $value = $computer($existing);
                } else {
                    /** @psalm-suppress MixedAssignment */
                    $value = $computer();
                }

                $this->driver->set($key, $value, $ttl);

                return $value;
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        /** @var (Closure(): mixed) $computer */
        $computer = static fn (): never => throw new Exception\UnavailableItemException(
            'The value associated with the key "' . $key . '" is not available.',
        );

        /** @psalm-suppress MissingThrowsDocblock */
        return $this->compute($key, $computer);
    }

    /**
     * Gets a value associated with the given key.
     *
     * If the specified key doesn't exist, `$computer` will be used to compute the value,
     * which will be stored in cache, and returned as a result of this method.
     *
     * Implementations must ensure that all pending operations related to the given key are completed before computing the value.
     *
     * @template T
     *
     * @param non-empty-string $key
     * @param Closure(): T $computer
     * @param positive-int|null $ttl
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     * @throws Exception\RuntimeException If an error occurs while getting the value.
     * @throws Exception\InvalidValueException If the value return from $computer cannot be stored in cache.
     *
     * @return T
     */
    public function compute(string $key, Closure $computer, null|int $ttl = null): mixed
    {
        /** @var T */
        return $this->sequence->waitFor($key, [$computer, $ttl, false]);
    }

    /**
     * Update the value associated with the unique key.
     *
     * Unlike {@see compute()}, `$computer` will always be invoked to compute the value.
     *
     * The resulted value will be stored, and returned as a result of this method.
     *
     * If `$key` doesn't exist in cache, it will be set.
     *
     * Implementations must ensure that all pending operations related to the given key are completed before computing the value.
     *
     * @template T
     *
     * @param non-empty-string $key
     * @param Closure(null|T): T $computer
     * @param positive-int|null $ttl
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     * @throws Exception\RuntimeException If an error occurs while getting the value.
     * @throws Exception\InvalidValueException If the value return from $computer cannot be stored in cache.
     *
     * @return T
     */
    public function update(string $key, Closure $computer, null|int $ttl = null): mixed
    {
        /** @var T */
        return $this->sequence->waitFor($key, [$computer, $ttl, true]);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): void
    {
        // wait for pending operations associated with the given key.
        $this->sequence->waitForPending($key);

        $this->driver->delete($key);
    }
}
