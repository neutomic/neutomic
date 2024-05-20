<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\ContentDelivery\Internal;

use Psl\SecureRandom;

final class Boundary
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
     * Cached boundary string.
     *
     * @var null|string
     */
    private static null|string $boundary = null;

    /**
     * Returns a boundary string for multipart/byteranges responses.
     */
    public static function get(): string
    {
        if (null === self::$boundary) {
            self::$boundary = SecureRandom\string(self::BOUNDARY_LENGTH, self::BOUNDARY_CHARACTERS);
        }

        return self::$boundary;
    }
}
