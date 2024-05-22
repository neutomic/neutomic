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

namespace Neu\Component\Cache\Driver;

use Neu\Component\Cache\Exception\InvalidValueException;
use Neu\Component\Cache\Exception\RuntimeException;
use Throwable;

/**
 * A trait to handle serialization and unserialization of values.
 *
 * @require-implements DriverInterface
 */
trait SerializationTrait
{
    /**
     * @throws InvalidValueException if the value cannot be serialized
     */
    protected function serialize(string $key, mixed $value): string
    {
        try {
            return serialize($value);
        } catch (Throwable $e) {
            throw new InvalidValueException('Failed to serialize value for key "' . $key . '"', 0, $e);
        }
    }

    /**
     * @throws RuntimeException if the value cannot be unserialized
     */
    protected function unserialize(string $key, string $value): mixed
    {
        if ('b:0;' === $value) {
            return false;
        }

        if ('N;' === $value) {
            return null;
        }

        /** @psalm-suppress MixedAssignment */
        $raw = @unserialize($value);
        if (false === $raw) {
            throw new RuntimeException('Failed to unserialize value for key "' . $key . '" ( ' . $value . ' )');
        }

        return $raw;
    }
}
