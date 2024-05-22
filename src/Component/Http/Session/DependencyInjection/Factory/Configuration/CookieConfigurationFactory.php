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

namespace Neu\Component\Http\Session\DependencyInjection\Factory\Configuration;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Message\CookieSameSite;
use Neu\Component\Http\Session\Configuration\CookieConfiguration;

/**
 * A factory to create a cookie configuration instance.
 *
 * @implements FactoryInterface<CookieConfiguration>
 */
final readonly class CookieConfigurationFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $name;
    private null|int $lifetime;
    private null|string $path;
    private null|string $domain;
    private null|bool $secure;
    private null|bool $httpOnly;
    private null|CookieSameSite $sameSite;

    /**
     * @param non-empty-string|null $name
     * @param int|null $lifetime
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool|null $httpOnly
     * @param CookieSameSite|string|null $sameSite
     */
    public function __construct(null|string $name = null, null|int $lifetime = null, null|string $path = null, null|string $domain = null, null|bool $secure = null, null|bool $httpOnly = null, null|string|CookieSameSite $sameSite = null)
    {
        if (null !== $sameSite && !$sameSite instanceof CookieSameSite) {
            $sameSite = CookieSameSite::from($sameSite);
        }

        $this->name = $name ?? CookieConfiguration::DEFAULT_NAME;
        $this->lifetime = $lifetime;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
    }


    public function __invoke(ContainerInterface $container): CookieConfiguration
    {
        return new CookieConfiguration(
            name: $this->name,
            lifetime: $this->lifetime,
            path: $this->path,
            domain: $this->domain,
            secure: $this->secure,
            httpOnly: $this->httpOnly,
            sameSite: $this->sameSite,
        );
    }
}
