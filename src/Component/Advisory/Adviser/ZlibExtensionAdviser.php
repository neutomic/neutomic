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

namespace Neu\Component\Advisory\Adviser;

use Neu\Component\Advisory\Advice;

use function extension_loaded;

/**
 * Adviser that provides advice on installing ext-zlib for enabling compression middleware.
 */
final readonly class ZlibExtensionAdviser implements AdviserInterface
{
    /**
     * Retrieve an advice instance regarding the ext-zlib extension.
     *
     * @return Advice|null An instance of Advice if ext-zlib is not installed, or null if it is installed.
     */
    public function getAdvice(): null|Advice
    {
        if (!extension_loaded('zlib')) {
            return Advice::forPerformance(
                'Install ext-zlib',
                'The ext-zlib extension is not installed. Installing it can enable HTTP compression middleware, improving performance.',
                'Install the ext-zlib extension and ensure it is enabled in your PHP configuration.'
            );
        }

        return null;
    }
}
