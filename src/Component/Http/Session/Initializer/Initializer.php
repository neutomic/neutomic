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

namespace Neu\Component\Http\Session\Initializer;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Session\Configuration\CookieConfiguration;
use Neu\Component\Http\Session\Session;
use Neu\Component\Http\Session\Storage\StorageInterface;

final readonly class Initializer implements InitializerInterface
{
    public function __construct(
        private StorageInterface $storage,
        private CookieConfiguration $cookie,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function initialize(RequestInterface $request): RequestInterface
    {
        $session = new Session([]);
        foreach ($request->getCookies() as $cookie => $values) {
            if ($cookie === $this->cookie->name) {
                $session = $this->storage->read($values[0]);

                break;
            }
        }

        return $request->withSession($session);
    }
}
