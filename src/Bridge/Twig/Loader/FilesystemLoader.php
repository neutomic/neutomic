<?php

declare(strict_types=1);

namespace Neu\Bridge\Twig\Loader;

use Amp\File;
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

        return File\getStatus($path)['mtime'];
    }
}
