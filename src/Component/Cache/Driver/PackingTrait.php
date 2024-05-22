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

use Neu\Component\Cache\Exception\InvalidKeyException;
use Neu\Component\Cache\Exception\InvalidValueException;
use Neu\Component\Cache\Exception\RuntimeException;
use Neu\Component\Cache\Exception\UnavailableItemException;

use function pack;
use function strlen;
use function substr;
use function unpack;

/**
 * @psalm-suppress UndefinedConstant - The constant is defined in the trait, but Psalm does not recognize it.
 */
trait PackingTrait
{
    use SerializationTrait;

    /**
     * The maximum value for a 32-bit unsigned integer.
     */
    protected const int MAXIMUM_VALUE = 0xFFFFFFFF;

    /**
     * The maximum length of a key.
     */
    protected const int MAXIMUM_KEY_LENGTH = 0xFFFF;

    /**
     * The number of bytes used to store the expiration time.
     */
    protected const int EXPIRATION_BYTE_LENGTH = 4;

    /**
     * The number of bytes used to store the key length.
     */
    protected const int KEY_LENGTH_BYTE_LENGTH = 4;

    /**
     * The number of bytes used to store the content length.
     */
    protected const int CONTENT_LENGTH_BYTE_LENGTH = 4;

    /**
     * The number of bytes used to store the header.
     */
    protected const int HEADER_BYTES_LENGTH = self::EXPIRATION_BYTE_LENGTH + self::KEY_LENGTH_BYTE_LENGTH + self::CONTENT_LENGTH_BYTE_LENGTH;

    /**
     * Packs a value for the cache.
     *
     * @param non-empty-string $key The key to pack.
     * @param mixed $value The value to pack.
     * @param null|int $ttl The expiration time of the value, or null if the value should not expire.
     *
     * @throws InvalidKeyException If the key is too long.
     * @throws InvalidValueException If the value cannot be serialized.
     *
     * @return string The packed value.
     */
    protected function pack(string $key, mixed $value, null|int $ttl = null): string
    {
        $serialized = $this->serialize($key, $value);
        if ($ttl !== null) {
            $expiration = time() + $ttl;
        } else {
            // Use maximum value for 32-bit unsigned int to represent 'never expires'
            $expiration = self::MAXIMUM_VALUE;
        }

        $keyLength = strlen($key);
        if ($keyLength > self::MAXIMUM_KEY_LENGTH) {
            throw InvalidKeyException::forLongKey($key, self::MAXIMUM_KEY_LENGTH);
        }

        $contentLength = strlen($serialized);
        // 'N' packs an unsigned long (32 bits, big endian) - used for expiration
        // 'N' packs an unsigned long (32 bits, big endian) - used for key length
        // 'N' packs an unsigned long (32 bits, big endian) - used for content length
        // 'a*' packs the string into a binary string - used for key
        // 'a*' packs the string into a binary string - used for content
        return pack('NNNa*a*', $expiration, $keyLength, $contentLength, $key, $serialized);
    }

    /**
     * Unpacks a value from the cache.
     *
     * @param non-empty-string $key The key of the packed value.
     * @param string $value The packed value.
     *
     * @throws RuntimeException If unpacking fails.
     * @throws UnavailableItemException If the item is expired.
     *
     * @return mixed The unpacked value.
     */
    protected function unpack(string $key, string $value): mixed
    {
        /** @var false|array{expires: int, key_length: int, content_length: int, data: string} $data */
        $data = unpack('Nexpires/Nkey_length/Ncontent_length/a*data', $value);
        if ($data === false) {
            throw new RuntimeException('Failed to unpack value from cache.');
        }

        // Check if expiration is set to 'never expire' or if the current time is less than expiration
        if ($data['expires'] === 0xFFFFFFFF || $data['expires'] > time()) {
            // Skip the key and read the content
            $content = substr($data['data'], $data['key_length']);

            return $this->unserialize($key, $content);
        }

        throw UnavailableItemException::for($key);
    }

    /**
     * Reads the header from a binary string.
     *
     * @param string $packedData The binary string from which to read the header.
     *
     * @throws RuntimeException If reading the header fails.
     *
     * @return array{expires: null|int, key_length: int, content_length: int} The header data.
     */
    protected function readHeader(string $packedData): array
    {
        /** @var false|array{expires: null|int, key_length: int, content_length: int} $data */
        $data = unpack('Nexpires/Nkey_length/Ncontent_length', substr($packedData, 0, self::HEADER_BYTES_LENGTH));
        if ($data === false) {
            throw new RuntimeException('Failed to read header from cache.');
        }

        $data['expires'] = $data['expires'] === self::MAXIMUM_VALUE ? null : $data['expires'];

        return $data;
    }
}
