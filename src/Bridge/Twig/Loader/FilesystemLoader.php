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

use Amp\File;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader as TwigFilesystemLoader;
use Twig\Source;

final class FilesystemLoader extends TwigFilesystemLoader implements ModificationAwareLoaderInterface
{
    /**
     * @inheritDoc
     */
    public function getSourceContext(string $name): Source
    {
        $path = (string) $this->findTemplate($name);

        return new Source(File\read($path), $name, $path);
    }

    /**
     * @inheritDoc
     */
    public function isFresh(string $name, int $time): bool
    {
        return $this->getLastModificationTime($name) <= $time;
    }

    /**
     * @inheritDoc
     */
    public function getLastModificationTime(string $name): int
    {
        $path = (string) $this->findTemplate($name);

        /** @var array{mtime: int}|null $status */
        $status = File\getStatus($path);
        if (null === $status) {
            throw new LoaderError('Template not found: ' . $name);
        }

        return $status['mtime'];
    }
}
