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

namespace Neu\Component\DependencyInjection;

use Neu\Component\DependencyInjection\Configuration\DocumentInterface;

/**
 * Represents an extension that is composed of multiple extensions.
 *
 * This interface is useful when you want to register multiple extensions
 * in a single class.
 *
 * Note that the {@see ExtensionInterface::register()} method of each extension
 * will be called in the order they are returned by {@see getExtensions()}.
 *
 * The {@see CompositeExtensionInterface::register()} method must not attempt to
 * register the individual extensions. Instead, it should only register additional
 * services, processors, etc. that are not provided by the individual extensions.
 */
interface CompositeExtensionInterface extends ExtensionInterface
{
    /**
     * Returns a list of {@see ExtensionInterface} instances.
     *
     * The extensions will be registered in the order they are returned.
     *
     * @param DocumentInterface $configurations The configuration document.
     *
     * @return list<ExtensionInterface> The extensions to be registered.
     */
    public function getExtensions(DocumentInterface $configurations): array;
}
