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

use Amp\Http\Internal\HPackNghttp2;
use FFI;
use Neu\Component\Advisory\Advice;

use function class_exists;
use function extension_loaded;

/**
 * Adviser that provides advice on ensuring HPack support via the nghttp2 library.
 */
final readonly class HPackNghttp2Adviser implements AdviserInterface
{
    /**
     * Retrieve an advice instance regarding HPack support via nghttp2.
     *
     * @return Advice|null An instance of Advice if HPack is not supported, or null if it is supported.
     */
    #[\Override]
    public function getAdvice(): null|Advice
    {
        if (!extension_loaded('ffi') || !class_exists(FFI::class)) {
            return Advice::forPerformance(
                'Enable FFI Extension for HPack Support',
                'The FFI extension is required for HPack support via the nghttp2 library, which can significantly enhance HTTP/2 performance.',
                'Install the FFI extension and ensure it is enabled in your PHP configuration.',
            );
        }

        /**
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        if (!HPackNghttp2::isSupported()) {
            return Advice::forPerformance(
                'Install and Configure nghttp2 for HPack Support',
                'HPack support via the nghttp2 library is currently not enabled. Enabling it can greatly improve HTTP/2 performance by providing efficient header compression.',
                'Install the nghttp2 library on your system and ensure it is properly configured. Refer to the nghttp2 installation guide for detailed instructions.'
            );
        }

        return null;
    }
}
