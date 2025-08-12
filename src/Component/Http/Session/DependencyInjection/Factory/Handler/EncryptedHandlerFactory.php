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

namespace Neu\Component\Http\Session\DependencyInjection\Factory\Handler;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Session\Handler\EncryptedHandler;
use SensitiveParameter;

/**
 * A factory to create an encrypted handler instance.
 *
 * @implements FactoryInterface<EncryptedHandler>
 */
final readonly class EncryptedHandlerFactory implements FactoryInterface
{
    /**
     * The encryption secret.
     *
     * @var null|non-empty-string
     */
    private null|string $secret;

    /**
     * @param null|non-empty-string $secret
     */
    public function __construct(#[SensitiveParameter] null|string $secret = null)
    {
        $this->secret = $secret;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container): EncryptedHandler
    {
        $secret = $this->secret ?? $container->getProject()->secret;

        return new EncryptedHandler($secret);
    }
}
