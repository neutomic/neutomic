<?php

declare(strict_types=1);

namespace Neu\Component\Cache;

use Closure;
use Psl\Async;

final class Store implements StoreInterface
{
    /**
     * @var Async\KeyedSequence<
     *      non-empty-string,
     *      array{Closure(): mixed, null|positive-int, false}|array{Closure(mixed): mixed, null|positive-int, true},
     *      mixed
     * >
     */
    private Async\KeyedSequence $sequence;

    public function __construct(private readonly Driver\DriverInterface $driver)
    {
        $this->sequence = new Async\KeyedSequence(
            /**
             * @param non-empty-string $key
             * @param array{Closure(): mixed, null|positive-int, bool} $input
             *
             * @return mixed
             */
            function (string $key, array $input): mixed {
                [$computer, $ttl, $update] = $input;

                $available = false;
                $existing = null;
                try {
                    $existing = $this->driver->get($key);
                    $available = true;
                } catch (Exception\UnavailableItemException) {
                }

                if ($available && !$update) {
                    return $existing;
                }

                if ($update) {
                    $value = $computer($existing);
                } else {
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
        return $this->compute(
            $key,
            static fn (): mixed => throw new Exception\UnavailableItemException('The value associated with the key "' . $key . '" is not available.'),
        );
    }

    /**
     * @inheritDoc
     */
    public function compute(string $key, Closure $computer, ?int $ttl = null): mixed
    {
        return $this->sequence->waitFor($key, [$computer, $ttl, false]);
    }

    /**
     * @inheritDoc
     */
    public function update(string $key, Closure $computer, ?int $ttl = null): mixed
    {
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
