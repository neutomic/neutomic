<?php

declare(strict_types=1);

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
