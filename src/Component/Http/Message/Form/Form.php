<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Form;

use Amp\Pipeline\ConcurrentIterator;

final readonly class Form implements FormInterface
{
    /**
     * @param ConcurrentIterator<FieldInterface> $fields
     */
    private ConcurrentIterator $fields;

    /**
     * Creates a new {@see Form} instance.
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
    public function getFields(): iterable
    {
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
