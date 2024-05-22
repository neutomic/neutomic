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

use Neu\Component\Http\Message\MessageInterface;

/**
 * Interface for individual form fields.
 *
 * Provides methods to access field-specific details like name, headers, and body.
 */
interface FieldInterface extends MessageInterface
{
    /**
     * Retrieves the name of the field.
     *
     * @return string The field name.
     */
    public function getName(): string;
}
