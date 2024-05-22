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

namespace Neu\Component\Http\Message\Form;

/**
 * Interface for file fields in a form.
 *
 * Extends {@see FieldInterface} with additional methods specific to file handling.
 */
interface FileInterface extends FieldInterface
{
    /**
     * Retrieves the original filename of the uploaded file.
     *
     * @return string The original name of the file as provided by the client.
     */
    public function getFilename(): string;

    /**
     * Retrieves the MIME type of the file.
     *
     * @return string The MIME type of the file.
     */
    public function getMimeType(): string;

    /**
     * Retrieves the extension of the file.
     *
     * @return null|string The extension of the file, or `null` if the extension is unknown.
     */
    public function getExtension(): null|string;
}
