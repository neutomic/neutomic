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
use Psl\Encoding\Base64;
use Psl\Str\Byte;
use Psl\Password;
use Psl\Hash;
use ValueError;

use const PASSWORD_ARGON2_DEFAULT_MEMORY_COST;
use const PASSWORD_ARGON2_DEFAULT_THREADS;
use const PASSWORD_ARGON2_DEFAULT_TIME_COST;
use const PASSWORD_BCRYPT_DEFAULT_COST;

/**
 * A native hasher implementation.
 *
 * @psalm-type Algorithm = 'default'|'bcrypt'|'argon2i'|'argon2id'
 * @psalm-type Options = array{cost?: int}|array{memory_cost?: int, time_cost?: int, threads?: int}
 */
final readonly class NativeHasher implements HasherInterface
{
    /**
     * The maximum password length.
     */
    private const int MAX_PASSWORD_LENGTH = 4096;

    /**
     * The default hashing algorithm.
     *
     * @var Algorithm
     */
    public const string DEFAULT_ALGORITHM = 'default';

    /**
     * The default hashing algorithm options.
     *
     * @var Options
     */
    public const array DEFAULT_OPTIONS = [
        'cost' => PASSWORD_BCRYPT_DEFAULT_COST,
        'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
        'time_cost' => PASSWORD_ARGON2_DEFAULT_TIME_COST,
        'threads' => PASSWORD_ARGON2_DEFAULT_THREADS,
    ];

    /**
     * The hashing algorithm.
     *
     * @var Algorithm
     */
    private string $algorithm;

    /**
     * The hashing algorithm options.
     *
     * @var Options
     */
    private array $options;

    /**
     * Construct a new {@see NativeHasher} instance.
     *
     * @param Algorithm $algorithm The hashing algorithm.
     * @param Options $options The hashing algorithm options.
     */
    public function __construct(string $algorithm = self::DEFAULT_ALGORITHM, array $options = self::DEFAULT_OPTIONS)
    {
        $this->algorithm = $algorithm;
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    final public function hashPassword(#[SensitiveParameter] string $plainPassword): string
    {
        if (Byte\length($plainPassword) > self::MAX_PASSWORD_LENGTH || Byte\contains($plainPassword, "\0")) {
            throw new Exception\InvalidArgumentException('The password is too long.');
        }

        try {
            return Password\hash(
                Base64\encode(Hash\hash($plainPassword, Hash\Algorithm::Sha384)),
                Password\Algorithm::from($this->algorithm),
                $this->options,
            );
        } catch (ValueError | Hash\Exception\ExceptionInterface $e) {
            throw new Exception\RuntimeException('Failed to hash the password.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    final public function verifyPassword(string $hashedPassword, #[SensitiveParameter] string $plainPassword): bool
    {
        try {
            return Password\verify(
                Base64\encode(Hash\hash($plainPassword, Hash\Algorithm::Sha384)),
                $hashedPassword,
            );
        } catch (Hash\Exception\ExceptionInterface) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    final public function passwordNeedsRehash(string $hashedPassword): bool
    {
        return Password\needs_rehash(
            $hashedPassword,
            Password\Algorithm::from($this->algorithm),
            $this->options,
        );
    }
}
