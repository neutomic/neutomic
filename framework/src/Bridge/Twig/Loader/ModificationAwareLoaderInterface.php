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

namespace Neu\Bridge\Twig\Loader;

use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;

/**
 * Interface for loaders that can track template modification times.
 */
interface ModificationAwareLoaderInterface extends LoaderInterface
{
    /**
     * Get the last modification time of a template.
     *
     * @param string $name The name of the template to check
     *
     * @throws LoaderError When $name is not found
     *
     * @return int The timestamp of the last modification time
     */
    public function getLastModificationTime(string $name): int;
}
