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
use Psl\Hash;
use Override;

/**
 * An abstract native hasher implementation.
 */
final readonly class PlainHasher implements HasherInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    final public function hashPassword(#[SensitiveParameter] string $plainPassword): string
    {
        return $plainPassword;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    final public function verifyPassword(string $hashedPassword, #[SensitiveParameter] string $plainPassword): bool
    {
        return Hash\equals($hashedPassword, $plainPassword);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    final public function passwordNeedsRehash(string $hashedPassword): bool
    {
        return false;
    }
}
