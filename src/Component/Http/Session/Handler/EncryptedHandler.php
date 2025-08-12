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

namespace Neu\Component\Http\Session\Handler;

use Neu\Component\Http\Session\Exception\InvalidArgumentException;
use Neu\Component\Http\Session\Exception\InvalidIdentifierException;
use Neu\Component\Http\Session\Exception\RuntimeException;
use Neu\Component\Http\Session\Session;
use Neu\Component\Http\Session\SessionInterface;
use SensitiveParameter;
use Psl\SecureRandom;
use Psl\Json;
use Psl\Encoding;
use Psl\Str;
use Override;

use function hash_equals;
use function pack;
use function sodium_crypto_generichash;
use function sodium_crypto_stream_xor;
use function sodium_memzero;

use const SODIUM_CRYPTO_AUTH_KEYBYTES;
use const SODIUM_CRYPTO_GENERICHASH_BYTES;
use const SODIUM_CRYPTO_GENERICHASH_BYTES_MAX;
use const SODIUM_CRYPTO_GENERICHASH_KEYBYTES;
use const SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
use const SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
use const SODIUM_CRYPTO_STREAM_NONCEBYTES;

/**
 * @psalm-suppress InvalidArgument
 * @psalm-suppress MoreSpecificReturnType
 * @psalm-suppress ArgumentTypeCoercion
 * @psalm-suppress MissingThrowsDocblock
 * @psalm-suppress LessSpecificReturnStatement
 */
final readonly class EncryptedHandler implements HandlerInterface
{
    /**
     * The encryption key.
     *
     * @var non-empty-string
     */
    private string $key;

    /**
     * Create a new EncryptedStorage instance.
     *
     * @param non-empty-string $key The encryption key.
     */
    public function __construct(#[SensitiveParameter] string $key)
    {
        $this->key = $key;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function load(string $identifier): SessionInterface
    {
        // Decode the message from a URL-safe base64 format
        try {
            $ciphertext = Encoding\Base64\decode($identifier, variant: Encoding\Base64\Variant::UrlSafe, explicit_padding: false);
        } catch (Encoding\Exception\ExceptionInterface $e) {
            throw InvalidIdentifierException::for($identifier, previous: $e);
        }

        try {
            // Unpack the ciphertext
            $pieces = $this->unpack($ciphertext);
        } catch (InvalidArgumentException $e) {
            // Wipe the ciphertext from memory
            sodium_memzero($ciphertext);

            throw InvalidIdentifierException::for($identifier, previous: $e);
        }

        $salt = $pieces[0];
        $nonce = $pieces[1];
        $encrypted = $pieces[2];
        $auth = $pieces[3];

        // Split our key into two keys: One for encryption, the other for
        // authentication. By using separate keys, we can reasonably dismiss
        // likely cross-protocol attacks.
        // This uses salted HKDF to split the keys, which is why we need the
        // salt in the first place. */
        [$encryptionKey, $authenticationKey] = $this->split($this->key, $salt);

        // Check the MAC first
        if (!$this->verify($salt . $nonce . $encrypted, $authenticationKey, $auth)) {
            // Wipe every superfluous piece of data from memory
            sodium_memzero($salt);
            sodium_memzero($nonce);
            sodium_memzero($encrypted);
            sodium_memzero($authenticationKey);
            sodium_memzero($encryptionKey);

            throw InvalidIdentifierException::for($identifier);
        }

        sodium_memzero($salt);
        sodium_memzero($authenticationKey);
        // crypto_stream_xor() can be used to encrypt and decrypt
        $plaintext = sodium_crypto_stream_xor($encrypted, $nonce, $encryptionKey);
        sodium_memzero($encrypted);
        sodium_memzero($nonce);
        sodium_memzero($encryptionKey);

        /**
         * Decode the JSON string.
         *
         * @var array<non-empty-string, mixed> $data
         */
        $data = Json\decode($plaintext);

        return new Session($data, $identifier);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function flush(string $identifier): void
    {
        // No need to flush the session data.
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function save(SessionInterface $session, null|int $ttl = null): string
    {
        try {
            // Convert the session data to a JSON string
            $plaintext = Json\encode($session->all());
        } catch (Json\Exception\ExceptionInterface $e) {
            throw new RuntimeException('Failed to encode session data to JSON', previous: $e);
        }

        // Generate a nonce and HKDF salt:
        $nonce = SecureRandom\string(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $salt = SecureRandom\string(32);

        // Split our key into two keys: One for encryption, the other for
        // authentication. By using separate keys, we can reasonably dismiss
        // likely cross-protocol attacks.
        // This uses salted HKDF to split the keys, which is why we need the
        // salt in the first place.
        [$encryptionKey, $authenticationKey] = $this->split($this->key, $salt);

        // Encrypt our message with the encryption key:
        $encrypted = sodium_crypto_stream_xor($plaintext, $nonce, $encryptionKey);

        // Wipe the encryption key from memory
        sodium_memzero($encryptionKey);

        // Calculate an authentication tag:
        $auth = sodium_crypto_generichash($salt . $nonce . $encrypted, $authenticationKey, SODIUM_CRYPTO_GENERICHASH_BYTES_MAX);

        // wipe authentication key from memory
        sodium_memzero($authenticationKey);

        // Concatenate the salt, nonce, encrypted message, and authentication tag
        $message = $salt . $nonce . $encrypted . $auth;

        // Wipe every superfluous piece of data from memory
        sodium_memzero($nonce);
        sodium_memzero($salt);
        sodium_memzero($encrypted);
        sodium_memzero($auth);

        // Encode the message in a URL-safe base64 format
        $encoded = Encoding\Base64\encode($message, variant: Encoding\Base64\Variant::UrlSafe, padding: false);

        // Wipe message from memory
        sodium_memzero($message);

        return $encoded;
    }

    /**
     * Split a key into two keys using HKDF.
     *
     * @param non-empty-string $key The key to split.
     * @param non-empty-string $salt The salt to use.
     *
     * @throws RuntimeException If an unknown error occurs.
     *
     * @return array{0: non-empty-string, 1: non-empty-string} The encryption and authentication keys.
     */
    private function split(#[SensitiveParameter] string $key, #[SensitiveParameter]  string $salt): array
    {
        $encryptionKey = $this->blake2b($key, for_encryption: true, info: 'neutomic:session:storage:encryption', salt: $salt);
        $authenticationKey = $this->blake2b($key, for_encryption: false, info: 'neutomic:session:storage:authentication', salt: $salt);

        return [$encryptionKey, $authenticationKey];
    }

    /**
     * Unpack ciphertext for decryption.
     *
     * @param non-empty-string $ciphertext The ciphertext to unpack.
     *
     * @throws InvalidArgumentException If the message is too short.
     *
     * @return array{
     *     0: non-empty-string,
     *     1: non-empty-string,
     *     2: non-empty-string,
     *     3: non-empty-string,
     * } The salt, nonce, encrypted message, and authentication tag.
     */
    private function unpack(#[SensitiveParameter] string $ciphertext): array
    {
        $length = Str\length($ciphertext, encoding: Str\Encoding::Ascii8bit);
        // Fail fast on invalid messages
        if ($length < SODIUM_CRYPTO_GENERICHASH_BYTES) {
            throw new InvalidArgumentException('Message is too short');
        }

        // The salt is used for key splitting (via HKDF)
        $salt = Str\slice($ciphertext, 0, SODIUM_CRYPTO_GENERICHASH_BYTES, encoding: Str\Encoding::Ascii8bit);

        // This is the nonce (we authenticated it):
        $nonce = Str\slice(
            $ciphertext,
            // 32:
            SODIUM_CRYPTO_GENERICHASH_BYTES,
            // 24:
            SODIUM_CRYPTO_STREAM_NONCEBYTES,
            encoding: Str\Encoding::Ascii8bit
        );

        // This is the crypto_stream_xor()ed ciphertext
        $encrypted = Str\slice(
            $ciphertext,
            // 56:
            SODIUM_CRYPTO_GENERICHASH_BYTES + SODIUM_CRYPTO_STREAM_NONCEBYTES,
            // $length - 120
            $length -
            (
                SODIUM_CRYPTO_GENERICHASH_BYTES + // 32
                SODIUM_CRYPTO_STREAM_NONCEBYTES + // 56
                SODIUM_CRYPTO_GENERICHASH_BYTES_MAX // 120
            ),
            encoding: Str\Encoding::Ascii8bit
        );

        // $auth is the last 32 bytes
        $auth = Str\slice($ciphertext, $length - SODIUM_CRYPTO_GENERICHASH_BYTES_MAX, encoding: Str\Encoding::Ascii8bit);

        // We don't need this anymore.
        sodium_memzero($ciphertext);

        // Now we return the pieces in a specific order:
        return [$salt, $nonce, $encrypted, $auth];
    }

    /**
     * Verify a message authentication code.
     *
     * @param non-empty-string $message The message to verify.
     * @param non-empty-string $key The key to use.
     * @param non-empty-string $mac The message authentication code.
     *
     * @return bool True if the MAC is valid, false otherwise.
     */
    private function verify(#[SensitiveParameter] string $message, #[SensitiveParameter]  string $key, #[SensitiveParameter]  string $mac): bool
    {
        // Calculate the MAC
        $calculated = sodium_crypto_generichash($message, $key, SODIUM_CRYPTO_GENERICHASH_BYTES_MAX);

        // Compare the MACs in constant time
        $result = hash_equals($mac, $calculated);

        // Wipe the calculated MAC from memory
        sodium_memzero($calculated);

        return $result;
    }

    /**
     * HKDF implementation using BLAKE2b.
     *
     * @param non-empty-string $ikm The input keying material.
     * @param bool $for_encryption Whether the output keying material is for encryption, or authentication.
     * @param non-empty-string $info The context information.
     * @param non-empty-string $salt The salt.
     *
     * @throws RuntimeException If an unknown error occurs.
     *
     * @return non-empty-string The output keying material.
     */
    private function blake2b(#[SensitiveParameter] string $ikm, bool $for_encryption, #[SensitiveParameter] string $info, #[SensitiveParameter] string $salt): string
    {
        if ($for_encryption) {
            $length = SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
        } else {
            $length = SODIUM_CRYPTO_AUTH_KEYBYTES;
        }

        // HKDF-Extract:
        // PRK = HMAC-Hash(salt, IKM)
        // The salt is the HMAC key.
        $prk = sodium_crypto_generichash($ikm, $salt, SODIUM_CRYPTO_GENERICHASH_BYTES);

        // HKDF-Expand:
        // This check is useless, but it serves as a reminder to the spec.
        if (Str\length($prk, encoding: Str\Encoding::Ascii8bit) < SODIUM_CRYPTO_GENERICHASH_KEYBYTES) {
            throw new RuntimeException('An unknown error has occurred');
        }

        // T(0) = ''
        $t = '';
        $last_block = '';
        for ($block_index = 1; Str\length($t, encoding: Str\Encoding::Ascii8bit) < $length; ++$block_index) {
            // T(i) = HMAC-Hash(PRK, T(i-1) | info | 0x??)
            $last_block = sodium_crypto_generichash($last_block . $info . pack('C', $block_index), $prk, SODIUM_CRYPTO_GENERICHASH_BYTES);

            // T = T(1) | T(2) | T(3) | ... | T(N)
            $t .= $last_block;
        }

        // ORM = first L octets of T
        $orm = Str\slice($t, 0, $length, encoding: Str\Encoding::Ascii8bit);

        // Wipe every superfluous piece of data from memory
        sodium_memzero($prk);
        sodium_memzero($t);
        sodium_memzero($last_block);

        return $orm;
    }
}
