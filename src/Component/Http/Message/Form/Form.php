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

use Psl\Vec;

/**
 * Represents a form containing fields.
 */
final readonly class Form implements FormInterface
{
    /**
     * @var list<FieldInterface>
     */
    private array $fields;

    /**
     * Creates a new {@see Form} instance.
     *
     * @param list<FieldInterface> $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getFiles(): array
    {
        /** @var list<FileInterface> */
        return Vec\filter(
            $this->fields,
            static fn (FieldInterface $field) => $field instanceof FileInterface,
        );
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getFieldsByName(string $name): array
    {
        return Vec\filter(
            $this->fields,
            static fn (FieldInterface $field) => $field->getName() === $name,
        );
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getFirstFieldByName(string $name): null|FieldInterface
    {
        foreach ($this->fields as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function hasFieldWithName(string $name): bool
    {
        return null !== $this->getFirstFieldByName($name);
    }
}
