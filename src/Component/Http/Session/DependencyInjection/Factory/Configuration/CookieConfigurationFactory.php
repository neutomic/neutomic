<?php

declare(strict_types=1);

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
    private string $name;
    private null|int $lifetime;
    private null|string $path;
    private null|string $domain;
    private null|bool $secure;
    private null|bool $httpOnly;
    private null|CookieSameSite $sameSite;

    public function __construct(?string $name = null, ?int $lifetime = null, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httpOnly = null, ?CookieSameSite $sameSite = null)
    {
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
