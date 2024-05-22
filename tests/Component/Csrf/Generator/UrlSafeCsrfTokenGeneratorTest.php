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

namespace Neu\Tests\Component\Csrf\Generator;

use Neu\Component\Csrf\Generator\UrlSafeCsrfTokenGenerator;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;
use Psl\Vec;

final class UrlSafeCsrfTokenGeneratorTest extends TestCase
{
    private UrlSafeCsrfTokenGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new UrlSafeCsrfTokenGenerator();
    }

    public function testGenerateTokenLength(): void
    {
        $token = $this->generator->generate();

        // 32 bytes * (4/3) for base64 - padding
        static::assertEquals(43, Byte\length($token));
    }

    public function testGenerateTokenIsUrlSafe(): void
    {
        $token = $this->generator->generate();

        static::assertStringNotContainsString('+', $token);
        static::assertStringNotContainsString('/', $token);
        static::assertStringNotContainsString('=', $token);
    }

    public function testGenerateTokenIsRandom(): void
    {
        $token1 = $this->generator->generate();
        $token2 = $this->generator->generate();

        static::assertNotSame($token1, $token2);
    }

    public function testGenerateTokenMultipleTimes(): void
    {
        $tokens = [];
        for ($i = 0; $i < 1000; $i++) {
            $tokens[] = $this->generator->generate();
        }

        static::assertCount(1000, Vec\unique_scalar($tokens));
    }
}
