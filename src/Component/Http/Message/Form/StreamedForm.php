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

use Amp\Pipeline\ConcurrentIterator;

final readonly class StreamedForm implements StreamedFormInterface
{
    /**
     * @param ConcurrentIterator<FieldInterface> $fields
     */
    private ConcurrentIterator $fields;

    /**
     * Creates a new {@see StreamedForm} instance.
     *
     * @param ConcurrentIterator<FieldInterface> $fields
     */
    public function __construct(ConcurrentIterator $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getFields(): iterable
    {
        /** @var FieldInterface $field */
        foreach ($this->fields as $field) {
            yield $field;
        }
    }

    public function __destruct()
    {
        // Disposes of the form fields.
        $this->fields->dispose();
    }
}
