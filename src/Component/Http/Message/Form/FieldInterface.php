<?php

declare(strict_types=1);

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
