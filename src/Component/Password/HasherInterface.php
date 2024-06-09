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

namespace Neu\Component\Password;

use SensitiveParameter;

/**
 * Interface for password hashing and verification.
 *
 * This interface defines the contract for handling password operations.
 *
 * Implementations are responsible for the specific hashing algorithm and security measures.
 */
interface HasherInterface
{
    /**
     * Hashes a plaintext password.
     *
     * This method takes a plaintext password as input and returns a hashed representation.
     *
     * The specific hashing algorithm and its parameters are determined by the implementation.
     *
     * @param string $plainPassword The plaintext password to hash.
     *
     * @throws Exception\InvalidArgumentException If the password is invalid, e.g. empty, too long, or contains a null byte.
     * @throws Exception\RuntimeException If the password could not be hashed.
     *
     * @return string The hashed password.
     */
    public function hashPassword(#[SensitiveParameter] string $plainPassword): string;

    /**
     * Verifies if a plaintext password matches a hashed password.
     *
     * This method compares a plaintext password with a previously hashed password.
     *
     * Implementations should use secure comparison techniques to prevent timing attacks.
     *
     * @param string $hashedPassword The hashed password to compare against.
     * @param string $plainPassword The plaintext password to verify.
     *
     * @return bool True if the passwords match, false otherwise.
     */
    public function verifyPassword(string $hashedPassword, #[SensitiveParameter] string $plainPassword): bool;

    /**
     * Checks if a hashed password needs to be rehashed.
     *
     * This method allows implementations to indicate whether a hashed password should be
     * rehashed using a potentially updated algorithm or configuration.
     *
     * The criteria for determining when a password needs to be rehashed is implementation-specific.
     *
     * @param string $hashedPassword The hashed password to check.
     *
     * @return bool True if the password needs rehashing, false otherwise.
     */
    public function passwordNeedsRehash(string $hashedPassword): bool;
}
