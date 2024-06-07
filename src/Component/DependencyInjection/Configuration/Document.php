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

namespace Neu\Component\DependencyInjection\Configuration;

use ArrayIterator;
use BackedEnum;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Exception\InvalidEntryException;
use Neu\Component\DependencyInjection\Exception\MissingEntryException;
use Psl\Iter;
use Psl\Type;
use Psl\Type\TypeInterface;
use Psl\Vec;
use Throwable;

use function array_merge;
use function array_merge_recursive;
use function array_replace;
use function array_replace_recursive;

final readonly class Document implements DocumentInterface
{
    /**
     * @param array<array-key, mixed> $entries
     */
    public function __construct(
        private array $entries,
        private bool $strict = false,
    ) {
    }

    /**
     * Create a new configuration container.
     *
     * @param array<array-key, mixed> $entries
     */
    public static function create(array $entries = [], bool $strict = false): static
    {
        return new self($entries, $strict);
    }

    /**
     * @inheritDoc
     */
    public function isStrict(): bool
    {
        return $this->strict;
    }

    /**
     * @inheritDoc
     */
    public function has(string|int $index): bool
    {
        return Iter\contains_key($this->entries, $index);
    }

    /**
     * @inheritDoc
     */
    public function get(string|int $index): mixed
    {
        if (!$this->has($index)) {
            throw $this->missingEntry($index);
        }

        return $this->entries[$index];
    }

    /**
     * @inheritDoc
     */
    public function getOfType(string|int $index, Type\TypeInterface $type): mixed
    {
        $value = $this->get($index);
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if ($this->strict) {
            try {
                return $type->assert($value);
            } catch (Type\Exception\AssertException $e) {
                throw $this->invalidEntry($index, 'is not of the expected type "' . $type->toString() . '"', $e);
            }
        }

        try {
            return $type->coerce($value);
        } catch (Type\Exception\CoercionException $e) {
            throw $this->invalidEntry($index, 'cannot be coerced into the expected type "' . $type->toString() . '"', $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getOfTypeOrDefault(int|string $index, TypeInterface $type, mixed $default): mixed
    {
        if (!$this->has($index)) {
            return $default;
        }

        /** @psalm-suppress MissingThrowsDocblock */
        return $this->getOfType($index, $type);
    }

    /**
     * @inheritDoc
     */
    public function isOfType(string|int $index, Type\TypeInterface $type): bool
    {
        if ($this->strict && (!$this->has($index) || !$type->matches($this->entries[$index]))) {
            return false;
        }

        try {
            $this->getOfType($index, $type);

            return true;
        } catch (ExceptionInterface) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getDocument(int|string $index, null|bool $strict = null): DocumentInterface
    {
        if (!$this->has($index)) {
            return new self([], $strict ?? $this->strict);
        }

        $result = $this->getOfType($index, Type\dict(Type\array_key(), Type\mixed()));

        return new self($result, $strict ?? $this->strict);
    }

    /**
     * @inheritDoc
     */
    public function isDocument(int|string $index): bool
    {
        if (!$this->has($index)) {
            return true; // empty document
        }

        return $this->isOfType($index, Type\dict(Type\array_key(), Type\mixed()));
    }

    /**
     * @inheritDoc
     */
    public function getIndices(): array
    {
        return Vec\keys($this->entries);
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->entries;
    }

    /**
     * @inheritDoc
     */
    public function combine(DocumentInterface $document, CombineStrategy $strategy): static
    {
        return match ($strategy) {
            CombineStrategy::Merge => $this->merge($document, recursive: false),
            CombineStrategy::MergeRecursive => $this->merge($document, recursive: true),
            CombineStrategy::Replace => $this->replace($document, recursive: false),
            CombineStrategy::ReplaceRecursive => $this->replace($document, recursive: true),
        };
    }

    /**
     * @inheritDoc
     */
    public function replace(DocumentInterface $document, bool $recursive = true): static
    {
        if ($recursive) {
            return new self(
                array_replace_recursive($this->entries, $document->getAll()),
                $this->strict || $document->isStrict()
            );
        }

        return new self(
            array_replace($this->entries, $document->getAll()),
            $this->strict || $document->isStrict()
        );
    }

    /**
     * @inheritDoc
     */
    public function merge(DocumentInterface $document, bool $recursive = true): static
    {
        if ($recursive) {
            return new self(
                array_merge_recursive($this->entries, $document->getAll()),
                $this->strict || $document->isStrict()
            );
        }

        return new self(
            array_merge($this->entries, $document->getAll()),
            $this->strict || $document->isStrict()
        );
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return Iter\count($this->entries);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->entries);
    }

    private function missingEntry(int|string $index): MissingEntryException
    {
        if (Type\string()->matches($index)) {
            $index = '"' . $index . '"';
        } else {
            $index = (string) $index;
        }

        return new MissingEntryException('Entry ' . $index . ' does not exist within the container.');
    }

    private function invalidEntry(int|string $index, string $message, null|Throwable $previous = null): InvalidEntryException
    {
        if (Type\string()->matches($index)) {
            $index = '"' . $index . '"';
        } else {
            $index = (string) $index;
        }

        return new InvalidEntryException('Entry ' . $index . ' value ' . $message, previous: $previous);
    }
}
