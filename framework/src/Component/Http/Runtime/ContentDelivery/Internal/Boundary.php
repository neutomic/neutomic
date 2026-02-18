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

namespace Neu\Component\Http\Runtime\ContentDelivery\Internal;

use Psl\SecureRandom;

enum Boundary
{
    /**
     * Length of the generated boundary string for multipart/byteranges responses.
     */
    private const int BOUNDARY_LENGTH = 60;

    /**
     * Allowed characters for generating a random boundary string for multipart responses.
     */
    private const string BOUNDARY_CHARACTERS = 'abcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * Returns a boundary string for multipart/byteranges responses.
     *
     * @psalm-suppress MissingThrowsDocblock
     *
     * @return non-empty-string
     */
    public static function get(): string
    {
        /** @var null|non-empty-string $boundary */
        static $boundary = null;

        if (null === $boundary) {
            /** @var non-empty-string $boundary */
            $boundary = SecureRandom\string(self::BOUNDARY_LENGTH, self::BOUNDARY_CHARACTERS);
        }

        return $boundary;
    }
}
