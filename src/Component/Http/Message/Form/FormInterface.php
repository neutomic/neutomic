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
 * Interface for handling form data from HTTP requests.
 *
 * This interface provides methods to access form fields regardless of the encoding type.
 *
 * It accommodates forms where multiple fields can have the same name by storing these fields in a list.
 */
interface FormInterface
{
    /**
     * Retrieves an iterator over all fields from the parsed form data.
     *
     * This method provides an efficient way to iterate over fields without loading them all into memory at once.
     *
     * It supports multiple fields with the same name, which are returned on separate iterations.
     *
     * @return iterable<FieldInterface> An iterable of {@see FieldInterface} implementations.
     */
    public function getFields(): iterable;
}
