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

namespace Neu\Component\Csrf\Generator;

use Neu\Component\Csrf\Exception\RuntimeException;
use Psl\Encoding\Base64;
use Psl\SecureRandom;

/**
 * A token generator implementation using {@see SecureRandom\bytes()}
 *  and {@see Base64\encode()} to generate cryptographically secure, URL-safe tokens.
 */
final readonly class UrlSafeCsrfTokenGenerator implements CsrfTokenGeneratorInterface
{
    private const int BYTES_LENGTH = 32;

    /**
     * @inheritDoc
     */
    #[\Override]
    public function generate(): string
    {
        try {
            $bytes = SecureRandom\bytes(self::BYTES_LENGTH);
        } catch (SecureRandom\Exception\InsufficientEntropyException $e) {
            throw new RuntimeException('Could not generate a CSRF token.', previous: $e);
        }

        /** @var non-empty-string */
        return Base64\encode($bytes, Base64\Variant::UrlSafe, padding: false);
    }
}
