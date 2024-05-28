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

namespace Neu\Component\Http\DependencyInjection;

use Neu\Component\DependencyInjection\CompositeExtensionInterface;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\Http\Message\DependencyInjection\MessageExtension;
use Neu\Component\Http\Recovery\DependencyInjection\RecoveryExtension;
use Neu\Component\Http\Router\DependencyInjection\RouterExtension;
use Neu\Component\Http\Runtime\DependencyInjection\RuntimeExtension;
use Neu\Component\Http\Server\DependencyInjection\ServerExtension;
use Neu\Component\Http\Session\DependencyInjection\SessionExtension;

/**
 * A composite extension that registers multiple HTTP-related extensions.
 *
 * This class implements the {@see CompositeExtensionInterface} to aggregate several
 * HTTP-related extensions and provide them as a single composite extension. The extensions
 * registered by this class include {@see MessageExtension}, {@see RecoveryExtension},
 * {@see RouterExtension}, {@see SessionExtension}, {@see RuntimeExtension}, and {@see ServerExtension}.
 */
final readonly class HttpExtension implements CompositeExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        // do nothing
    }

    /**
     * @inheritDoc
     */
    public function getExtensions(): array
    {
        return [
            new MessageExtension(),
            new RecoveryExtension(),
            new RouterExtension(),
            new SessionExtension(),
            new RuntimeExtension(),
            new ServerExtension(),
        ];
    }
}
